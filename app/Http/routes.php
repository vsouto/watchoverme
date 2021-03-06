<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['web']], function () {

    //Route::auth();

    Route::get('/',['as' => 'home', 'uses' => 'PagesController@home']);
    //Route::get('home',['as' => 'home', 'uses' => 'PagesController@home']);

    // Sobre
    Route::get('about',['as' => 'about', 'uses' => 'PagesController@about']);

    // Streams
    Route::get('gamers/streams',['as' => 'gamers.streams', 'uses' => 'GamersController@streams']);

    // API consuming
    Route::get('consult',['as' => 'consult', 'uses' => 'PagesController@consult']);

    // Subscribe
    Route::post('pages.subscribe',['as' => 'pages.subscribe', 'uses' => 'PagesController@subscribe']);

    // Inhouse
    Route::get('gamers.activate',['as' => 'gamers.activate', 'uses' => 'GamersController@activate']);
    Route::get('test/{channel?}',['as' => 'test', 'uses' => 'PagesController@test']);

    // Resources
    Route::resource('events','EventsController');
    Route::resource('gamers','GamersController');
    Route::resource('posts','PostsController');

    // Sitemaps
    Route::get('sitemap',['as' => 'sitemap', 'uses' => 'SitemapsController@index']);
    Route::get('sitemaps/general',['as' => 'sitemaps.general', 'uses' => 'SitemapsController@index']);
    Route::get('sitemaps/users',['as' => 'sitemaps.users', 'uses' => 'SitemapsController@users']);
    Route::get('sitemaps/posts',['as' => 'sitemaps.posts', 'uses' => 'SitemapsController@posts']);
    Route::get('sitemaps/teams',['as' => 'sitemaps.teams', 'uses' => 'SitemapsController@teams']);

    // Events
    Route::get('events.get',['as' => 'events.get', 'uses' => 'EventsController@get']);

    // Categories
    Route::get('posts/{category}',['as' => 'posts.category', 'uses' => 'CategoriesController@index']);


});

//Route::auth();

Route::auth();

// Users
Route::resource('users','UsersController');

Route::group(['middleware' => ['auth']], function () {


    Route::resource('teams','TeamsController');
    //Route::resource('inhouse','InhouseController');

    // User avatar upload
    Route::post('users/upload',['as' => 'users/upload', 'uses' => 'UsersController@upload']);
    Route::get('users.salvarTwitch',['as' => 'users.salvarTwitch', 'uses' => 'UsersController@salvarTwitch']);
    Route::post('teams/upload',['as' => 'teams/upload', 'uses' => 'TeamsController@upload']);

    // Team request to join
    Route::get('teams.request',['as' => 'teams.request', 'uses' => 'TeamsController@request']);
    Route::get('teams.aproveRequest',['as' => 'teams.aproveRequest', 'uses' => 'TeamsController@aproveRequest']);
    Route::get('teams.removePlayer',['as' => 'teams.removePlayer', 'uses' => 'TeamsController@removePlayer']);
    Route::get('teams.disband',['as' => 'teams.disband', 'uses' => 'TeamsController@disband']);

    // Create Team
    Route::get('teams/create',['as' => 'teams.create', 'uses' => 'TeamsController@create']);

    // Inhouse auth
    Route::post('pusher/auth', function (Illuminate\Http\Request $request, Pusher $pusher) {
        return $pusher->presence_auth(
            $request->input('channel_name'),
            $request->input('socket_id'),
            uniqid(),
            //['username' => $request->input('username')]
            ['username' => 'souto']
        );
    });

    // Inhouse
    Route::get('messages', ['as' => 'messages', 'uses' => 'InhouseController@listMessages']);
    Route::post('/messages', 'InhouseController@saveMessage');

    Route::get('inhouse/entrar', ['as' => 'inhouse.entrar', 'uses' => 'InhouseController@join']);
    Route::get('inhouse/ranking', ['as' => 'inhouse.ranking', 'uses' => 'InhouseController@ranking']);
    Route::get('inhouse/', ['as' => 'inhouse', 'uses' => 'InhouseController@index']);

    Route::get('inhouse/invite', ['as' => 'inhouse.invite', 'uses' => 'InhouseController@invite']);
    Route::post('inhouse/invite',['as' => 'inhouse.invite', 'uses' => 'InhouseController@doInvite']);

    //tests
    Route::get('inhouse/tests', ['as' => 'inhouse.tests', 'uses' => 'InhouseController@tests']);
    Route::get('inhouse/testGamer/{gamer_id}', ['as' => 'inhouse.testGamer', 'uses' => 'InhouseController@testGamer']);
    Route::get('inhouse/getLowestPlayer/{gamer_id}', ['as' => 'inhouse.getLowestPlayer', 'uses' => 'InhouseController@getLowestPlayer']);

    Route::get('inhouse/defineMatchRating/{match}', ['as' => 'inhouse.defineMatchRating', 'uses' => 'InhouseController@defineMatchRating']);
    Route::get('inhouse/getMatchs', ['as' => 'inhouse.getMatchs', 'uses' => 'InhouseController@getMatchs']);
    Route::get('inhouse/getOnlinePlayers', ['as' => 'inhouse.getOnlinePlayers', 'uses' => 'InhouseController@getOnlinePlayers']);

    // get colaborators
    Route::get('getColaborators', ['as' => 'getColaborators', 'uses' => 'UsersController@getColaborators']);

});


Route::get('getStatsPlayers', ['as' => 'getStatsPlayers', 'uses' => 'PagesController@getStatsPlayers']);
Route::get('getStatsTeams', ['as' => 'getStatsTeams', 'uses' => 'PagesController@getStatsTeams']);
Route::get('getStatsUpdates', ['as' => 'getStatsUpdates', 'uses' => 'PagesController@getStatsUpdates']);
Route::get('getStatsEvents', ['as' => 'getStatsEvents', 'uses' => 'PagesController@getStatsEvents']);


// Pusher auth
//Route::post('pusher/auth',['as' => 'pusher/auth', 'uses' => 'InhouseController@auth']);
//Route::get('pusher/auth',['as' => 'pusher/auth', 'uses' => 'InhouseController@auth']);

