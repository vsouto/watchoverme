<?php

namespace App\Http\Controllers;

use App\Event;
use App\Gamer;
use App\Post;
use App\Posts;
use App\Subscriber;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

use SEO;
use Sitemap;
use OpenGraph;
use SEOMeta;

class PagesController extends Controller
{

    public function home()
    {
        SEOMeta::setDescription('Últimas notícias de Overwatch, campeonatos, eventos, vídeos e tudo sobre a comunidade de Overwatch.');
        SEOMeta::setCanonical('http://watchoverme.com.br/');
        SEOMeta::addKeyword(['notícias', 'overwatch', 'o verme', 'verme', 'campeonatos overwatch', 'ranking overwatch', 'ranking nacional']);

        OpenGraph::setDescription('Últimas notícias de Overwatch, campeonatos, eventos e vídeos e tudo sobre a comunidade de Overwatch.');
        OpenGraph::setUrl('http://www.watchoverme.com.br/');
        //OpenGraph::addProperty('type', 'article');
        OpenGraph::addProperty('locale', 'pt-br');
        OpenGraph::addProperty('locale:alternate', ['pt-pt', 'en-us']);

        OpenGraph::addImage(asset('img/o-verme-jim.jpg'));

        Sitemap::addTag(route('home'), '', 'daily', '0.8');

        // Last Posts
        $posts = Posts::orderBy('created_at','DESC')
            ->take(8)
            ->get();

        // Last updated players
        $updated_players = Gamer::orderBy('updated_at', 'DESC')
            ->with('user')
            ->take(6)->get();

        // New registered users
        $new_registered_users = User::orderBy('created_at','DESC')
            ->take(8)->get();

        $date = new \DateTime();
        $date->modify('-30 days');
        $formatted_date = $date->format('Y-m-d H:i:s');

        // New users amount
        $count_new_users = User::where('created_at','>=',$formatted_date)->count();

        // Events later than today
        $events = Event::orderBy('starts','ASC')
            ->where('starts','>=', DB::raw('curdate()'))
            ->get();

        // Top 5 ranking
        $tops = Gamer::orderBy('competitive_rank', 'DESC')
            ->with('user')
            ->take(5)->get();

        return view('pages.home',compact(
            'posts','updated_players','new_registered_users','count_new_users','events','tops'));
    }


    public function soon()
    {
        return view('pages.coming-soon');
    }


    public function subscribe()
    {
        $data = Input::all();

        if (!empty($data['email'])) {

            Subscriber::create([
                'email' => $data['email']
            ]);

            return Response::json(true);
        }

        return Response::json(false);
    }

    public function sitemap()
    {
        // Get a general sitemap.
        Sitemap::addSitemap('/sitemap');

        // You can use the route helpers too.
        //Sitemap::addSitemap(URL::route('sitemap.posts'));
        //Sitemap::addSitemap(route('sitemaps.users'));

        // Return the sitemap to the client.
        return Sitemap::index();
    }

}
