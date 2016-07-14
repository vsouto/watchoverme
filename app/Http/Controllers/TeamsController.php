<?php

namespace App\Http\Controllers;

use App\Team;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Nayjest\Grids\Components\Base\RenderableRegistry;
use Nayjest\Grids\Components\FiltersRow;
use Nayjest\Grids\Components\HtmlTag;
use Nayjest\Grids\Components\RecordsPerPage;
use Nayjest\Grids\Components\RenderFunc;
use Nayjest\Grids\Components\TFoot;
use Nayjest\Grids\Components\THead;
use Nayjest\Grids\EloquentDataProvider;
use Nayjest\Grids\FieldConfig;
use Nayjest\Grids\Grid;
use Nayjest\Grids\GridConfig;

use SEO;

class TeamsController extends Controller
{
    /**
     * Show a list of users
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {

        // If user didnt defined filters or order, define order
        if ($request->query->count() <= 0) {
            // Define Query
            $query = (new Team())
                ->newQuery();
        }
        else {
            // Define Query
            $query = (new Team())
                ->newQuery();
        }

        // Define query
        $config = (new GridConfig())
            ->setName('Teams')
            ->setDataProvider(
                new EloquentDataProvider(
                    $query
                )
            )
            ->setPageSize(50)
            ->setColumns([
                (new FieldConfig('image'))
                    ->setLabel('Logo')
                    ->setSortable(true)
                    ->setCallback(function ($val, \Nayjest\Grids\EloquentDataRow $row) {

                        if (!empty($val))
                            return '<a href="'.route('teams.show',[$row->getSrc()->slug]).'">'
                                . '<img src="'.Team::$avatar_dir . $val .'" width="180px"></a>';
                    }),
                (new FieldConfig('title'))
                    ->setLabel('Time')
                    ->setSortable(true)
                    ->setCallback(function ($val, \Nayjest\Grids\EloquentDataRow $row) {

                        $span = '<a href="'.route('teams.show',[$row->getSrc()->slug]).'">' .
                            $val . '</a>';

                        return $span;
                    }),
                (new FieldConfig('players'))
                    ->setSortable(true)
                    ->setLabel('Jogadores')
                    ->setCallback(function ($val, \Nayjest\Grids\EloquentDataRow $row) {

                        $players = Team::where('id', $row->getSrc()->id)
                            ->first()->users()->count();

                        if (!empty($players))
                            return $players ;
                    }),
            ])
            ->setComponents([
                (new THead())
                    ->getComponentByName(FiltersRow::NAME)
                    ->setComponents([
                        (new RecordsPerPage())
                            ->setVariants([
                                50,
                                100,
                                200
                            ])
                            ->setRenderSection('filters_row_column_level')
                        ,
                        (new HtmlTag())
                            ->setTagName('button')
                            ->setAttributes([
                                'type' => 'submit',
                                'class' => 'btn btn-success btn-small'
                            ])
                            ->addComponent(new RenderFunc(function() {
                                return '<i class="glyphicon glyphicon-refresh"></i> Filter';
                            }))
                            ->setRenderSection('filters_row_column_level'),
                        (new HtmlTag)
                            ->setContent(' <i class="fa fa-arrows-alt"></i> All ')
                            ->setTagName('span')
                            ->setRenderSection(RenderableRegistry::SECTION_BEFORE)
                            ->setAttributes([
                                'class' => 'btn btn-warning btn-sm',
                                'id'    => 'show-all'
                            ])
                    ])
                    ->getParent()
                ,
                new TFoot()
            ]);

        $grid = (new Grid($config))->render();

        SEO::setTitle('Times brasileiros de OverWatch');
        SEO::setDescription('As principais equipes, cl�s e times de Overwatch do Brasil, encontre o seu time.');
        SEO::opengraph()->setUrl('http://watchoverme.com.br/teams');
        //SEO::setCanonical('https://codecasts.com.br/lesson');
        SEO::opengraph()->addProperty('type', 'articles');

        return view('teams.index', compact('grid'));
    }

    /**
     * Show element
     *
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($slug)
    {
        $team = Team::where('slug',$slug)
            ->firstOrFail();

        SEO::setTitle($team->title, '- Time brasileiro de OverWatch');
        SEO::setDescription($team->description);
        SEO::opengraph()->setUrl('http://watchoverme.com.br/teams/' . $team->slug);
        //SEO::setCanonical('https://codecasts.com.br/lesson');
        SEO::opengraph()->addProperty('type', 'articles');

        return view('teams.show', compact('team'));
    }

    /**
     * Upload team image
     *
     * @return mixed
     */
    public function upload() {

        $team_id = Input::get('team_id');

        // getting all of the post data
        $file = array('image' => Input::file('image'));
        // setting up rules
        $rules = array('image' => 'required',); //mimes:jpeg,bmp,png and for max size max:10000

        // doing the validation, passing post data, rules and the messages
        $validator = Validator::make($file, $rules);

        if ($validator->fails()) {
            // send back to the page with the input data and errors
            return Redirect::back()->withInput()->withErrors($validator);
        }
        else {
            // checking file is valid.
            if (Input::file('image')->isValid()) {

                $destinationPath = Team::$avatar_dir; // upload path
                $extension = Input::file('image')->getClientOriginalExtension(); // getting image extension
                $fileName = rand(11111,99999).'.'.$extension; // renameing image

                Input::file('image')->move($destinationPath, $fileName); // uploading file to given path

                Team::where('id',$team_id)->update([
                    'image' => $fileName
                ]);

                // sending back with message
                Session::flash('success', 'Upload successfully');
                return Redirect::back();
            }
            else {
                // sending back with error message.
                Session::flash('error', 'uploaded file is not valid');
                return Redirect::back();
            }
        }
    }

    /**
     * Requests to join team
     *
     */
    public function request()
    {
        $user_id = Auth::user()->id;
        $team_id = Input::get('team_id');

        // Check if user is not on the team
        if(User::isOnTeam($team_id))
            return Response::json(false);

        $request = \App\Request::create([
            'user_id' => $user_id,
            'team_id' => $team_id
        ]);

        return Response::json($request);
    }

    /**
     * Aproves a Request to join team
     *
     */
    public function aproveRequest()
    {
        $user_id = Input::get('user_id');
        $team_id = Input::get('team_id');

        $aprove = \App\Request::where('user_id',$user_id)
            ->where('team_id',$team_id)
            ->update([
                'aproved' => '1'
            ]);

        if ($aprove) {
            $team = Team::where('id',$team_id)->first();

            $team->users()->attach($user_id);

            return Response::json(true);
        }

        return Response::json(false);

    }

}
