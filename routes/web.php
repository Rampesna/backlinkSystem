<?php

Auth::routes([
    "register" => false
]);


Route::get('try-metadata','PremiumController@metadata');
Route::get('try-links','PremiumController@links');

Route::get('datee',function (){ return strtotime(date("Y-m-d H:i:s")); });

Route::get('google-api-trial','PremiumController@index')->middleware('auth');

Route::get('api-trials',function (){
    $queryString = http_build_query([
        'access_key' => '8e88b0ecb5406a75f182bfc35a708624',
        'query' => 'malatya web tasarim',
        'hl' => 'tr'
    ]);

    $ch = curl_init(sprintf('%s?%s', 'http://api.serpstack.com/search', $queryString));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $json = curl_exec($ch);
    curl_close($ch);

    $api_result = json_decode($json, true);

    return json_encode($api_result);

//    $returnString = "";
//
//    $returnString .= "Total results: ". $api_result['search_information']['total_results']. PHP_EOL."\n";
//
//    foreach ($api_result['organic_results'] as $number => $result) {
//        $returnString .= "{$number}. {$result['title']}". PHP_EOL."\n";
//    }
//    return $returnString;
})->middleware('auth');

Route::get('/', function () { return redirect()->route('home'); });
Route::get('/home', function () { return redirect()->route('home'); });
Route::get('/index', function (){
    $landingSettings = \App\Models\LandingSettingsTableModel::find(1);
    return view('welcome',compact('landingSettings'));
})->name('home');

Route::get('/link-control', 'CustomerController@linkControl')->name('link-control');
Route::get('/link-admin-control', 'MainController@linkAdminControl')->name('link-admin-control');
Route::get('/waiting-credits-control', 'MainController@waitingCreditsControl')->name('waiting-credits-control');
Route::get('/waiting-tickets-control', 'MainController@waitingTicketsControl')->name('waiting-tickets-control');

Route::get('register-form','RegisterController@registerForm')->name('register-form');
Route::post('register-post','RegisterController@registerPost')->name('register-post');
Route::get('verification-result/{status}','RegisterController@verificationResult')->name('verification-result');
Route::get('resend-verification','RegisterController@resendVerification')->name('resend-verification');
Route::get('email-verification/{token}','RegisterController@emailVerification')->name('email-verification');

Route::post('/ticket-answer','TicketsController@ticketAnswer')->name('ticket-answer');
Route::post('/resolve-ticket','TicketsController@resolveTicket')->name('resolve-ticket');

Route::get('/dashboard', 'MainController@index')->name('index');


Route::prefix('blog')->group(function (){
    Route::get('/',function (){ return redirect()->route('blog.index'); });
    Route::get('/page/{page?}','BlogController@index')->name('blog.index');
    Route::get('/post/{id?}/{title?}','BlogController@single')->name('blog.single');
    Route::post('/search','BlogController@search')->name('search');
    Route::get('/searchs/{keyword?}/{page?}','BlogController@searchKeyword')->name('keyword');
    Route::get('/category/{name}/{page}','BlogController@index')->name('blog.category');

    Route::post('/submit-comment','BlogController@submitComment')->name('submit-comment');
});



Route::middleware('admin-control')->group(function (){
    Route::prefix('account')->group(function (){
        Route::get('/all-accounts','AccountsController@allAccounts')->name('all-accounts');
        Route::get('/account-detail/{id}','AccountsController@accountDetail')->name('account-detail');

        Route::get('/edit-account/{id}','AccountsController@editAccount')->name('edit-account');
        Route::post('/update-account','AccountsController@updateAccount')->name('update-account');

        Route::get('/delete-account/{id}','AccountsController@deleteAccount')->name('delete-account');

        Route::get('/credit-appeals','CreditsController@creditAppeals')->name('credit-appeals');

        Route::post('/accept-appeal','CreditsController@acceptCreditAppeal')->name('accept-appeal');
        Route::post('/decline-appeal','CreditsController@declineCreditAppeal')->name('decline-appeal');

        Route::get('/sold-credits','CreditsController@soldCredits')->name('sold-credits');
        Route::get('/declined-credits','CreditsController@declinedCredits')->name('declined-credits');

        Route::get('/edit-user-site/{id}','AccountsController@editUserSite')->name('edit-user-site');
        Route::post('/update-user-site','AccountsController@updateUserSite')->name('update-user-site');

        Route::post('set-account-activated','AccountsController@setAccountActivated')->name('set-account-activated');
        Route::post('set-account-not-banned','AccountsController@setAccountNotBanned')->name('set-account-not-banned');
        Route::post('set-account-banned','AccountsController@setAccountBanned')->name('set-account-banned');
        Route::post('clear-credit','AccountsController@clearCredit')->name('clear-credit');
    });


// Links
    Route::prefix('links')->group(function (){

        Route::get('/all-links', 'LinkControllers@allLinks')->name('all-links');
        Route::get('/add-link', 'LinkControllers@addLink')->name('add-link');
        Route::post('/add-link-control', 'LinkControllers@addLinkControl')->name('add-link-control');
        Route::post('/add-link', 'LinkControllers@addLinkPost')->name('add-link-post');

        Route::get('/edit-link/{id}/{url}','LinkControllers@editLink')->name('edit-link');
        Route::post('/update-link','LinkControllers@updateLink')->name('update-link');

        Route::get('/delete-link/{id}','LinkControllers@deleteLink')->name('delete-link');

        Route::get('/key-code',function (){
            return view('Pages.Links.key-code');
        })->name('key-code');

        Route::get('/link-purchases/{id}','LinkControllers@linkPurchases')->name('link-purchases');

        Route::get('/links-of-waiting-to-be-add','LinkControllers@linksOfWaitingToBeAdd')->name('links-of-waiting-to-be-add');
        Route::post('/set-link-added','LinkControllers@setLinkAdded')->name('set-link-added');
    });

// Tickets
    Route::prefix('tickets')->group(function (){
        Route::get('/all-tickets/{page?}','TicketsController@allTickets')->name('all-tickets');
        Route::get('/pending-tickets/{page?}','TicketsController@pendingTickets')->name('pending-tickets');
        Route::get('/responded-tickets/{page?}','TicketsController@respondedTickets')->name('responded-tickets');
        Route::get('/resolved-tickets/{page?}','TicketsController@resolvedTickets')->name('resolved-tickets');

        Route::get('/show-ticket/{id}','TicketsController@showTicket')->name('show-ticket');
    });

    Route::prefix('settings')->group(function (){
        Route::get('landing-settings','SettingsController@landingSettings')->name('landing-settings');
        Route::post('update-landing-settings','SettingsController@updateLandingSettings')->name('update-landing-settings');
    });

    Route::prefix('admin-blog')->group(function (){
        Route::get('all-posts/{page?}','BlogAdminController@index')->name('all-posts');
        Route::get('add-post-form','BlogAdminController@addPostForm')->name('add-post-form');
        Route::post('add-post','BlogAdminController@addPost')->name('add-post');
        Route::get('edit-post/{id}','BlogAdminController@editPost')->name('edit-post');
        Route::post('update-post','BlogAdminController@updatePost')->name('update-post');
        Route::get('delete-post/{id}','BlogAdminController@deletePost')->name('delete-post');


        Route::get('all-categories/{page?}','BlogAdminController@categories')->name('all-categories');
        Route::get('add-category-form','BlogAdminController@addCategoryForm')->name('add-category-form');
        Route::post('add-category','BlogAdminController@addCategory')->name('add-category');
        Route::get('edit-category/{id}','BlogAdminController@editCategory')->name('edit-category');
        Route::post('update-category','BlogAdminController@updateCategory')->name('update-category');
        Route::get('delete-category/{id}','BlogAdminController@deleteCategory')->name('delete-category');


        Route::get('waiting-comments/{page?}','BlogAdminController@waitingComments')->name('waiting-comments');
        Route::post('confirm-comment','BlogAdminController@confirmComment')->name('confirm-comment');
        Route::get('delete-comment/{id}','BlogAdminController@deleteComment')->name('delete-comment');
    });

    Route::prefix('logs')->group(function (){
        Route::get('customer-logs/{page?}','MainController@customerLogs')->name('customer-logs');
    });

});

// Customer Area
Route::middleware('customer-control')->group(function (){
    Route::prefix('customer')->group(function (){
        Route::get('/all-links','CustomerController@allLinks')->name('customer-all-links');
        Route::get('/edit-my-link/{id}','CustomerController@editLink')->name('edit-my-link');
        Route::post('/update-my-link','CustomerController@updateLink')->name('update-my-link');

    });

    Route::prefix('my-account')->group(function (){
        Route::get('/my-account','CustomerController@myAccount')->name('my-account');

        Route::get('/my-sites','CustomerController@mySites')->name('my-sites');
        Route::get('/delete-site/{id}','CustomerController@deleteSite')->name('delete-site');
        Route::get('/add-site','CustomerController@addSite')->name('add-site');
        Route::post('/add-site-control','CustomerController@addSiteControl')->name('add-site-control');
        Route::post('/add-site-post','CustomerController@addSitePost')->name('add-site-post');
        Route::get('/edit-site/{id}','CustomerController@editSite')->name('edit-site');
        Route::post('/update-site','CustomerController@updateSite')->name('update-site');


        Route::get('/my-premium-sites','CustomerController@myPremiumSites')->name('my-premium-sites');
        Route::get('/add-premium-site','CustomerController@addPremiumSiteForm')->name('add-premium-site');
        Route::post('/add-premium-site-post','CustomerController@addPremiumSitePost')->name('add-premium-site-post');


        Route::get('/my-links','CustomerController@myLinks')->name('my-links');
        Route::get('/buy-link/{id}','CustomerController@buyLink')->name('buy-link');
        Route::post('/buy-link-post','CustomerController@buyLinkPost')->name('buy-link-post');

        Route::get('/buy-credit','CustomerController@buyCredit')->name('buy-credit');
        Route::post('/buy-credit-post','CustomerController@buyCreditPost')->name('buy-credit-post');

    });

    Route::prefix('my-tickets')->group(function (){

        Route::get('add-ticket','CustomerController@addTicket')->name('add-ticket');
        Route::post('add-ticket-post','CustomerController@addTicketPost')->name('add-ticket-post');

        Route::get('/my-all-tickets/{page?}','CustomerController@allTickets')->name('my-all-tickets');
        Route::get('/my-pending-tickets/{page?}','CustomerController@pendingTickets')->name('my-pending-tickets');
        Route::get('/my-responded-tickets/{page?}','CustomerController@respondedTickets')->name('my-responded-tickets');
        Route::get('/my-resolved-tickets/{page?}','CustomerController@resolvedTickets')->name('my-resolved-tickets');

        Route::get('/show-my-ticket/{id}','CustomerController@showTicket')->name('show-my-ticket');
    });

});

// Blog Area
Route::prefix('blog')->group(function (){
    Route::get('/index','BlogController@index')->name('blog.index');
    Route::get('/post/{id}/{title}','BlogController@post')->name('blog.post');
    Route::get('/search/{keyword}/{page}','BlogController@seach')->name('blog.search');
});
