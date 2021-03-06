<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
	//
});


App::after(function($request, $response)
{
	//
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest()) {
		//store the url they were trying to hit in session
        if(Request::segment(1) != "logout"){
		    Session::put('returnUrl', Request::url());
        }
		return Redirect::route('login')
			->with('flash_error', 'You must be logged in to view that page! You\'ll be redirected to your intended destination after logging in.');
	}
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::route('dashboard')->with('flash_success', 'You are already logged in!');;
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});

/*
|--------------------------------------------------------------------------
| Profile Filter
|--------------------------------------------------------------------------
|
| The Profile Filter ensures that the user has a profile before hitting 
| protected routes. Using this filter requires users to fill out their profile. :]
|
*/

Route::filter('profile', function()
{
    if(Auth::check()){
        //user is logged in
        $flash_notice = 'Please fill out your profile below to gain access to the rest of the site.';
        if( Auth::user()->role == "Admin" ) {
            if(is_null(Auth::user()->profile)){
                return Redirect::route('admins.create')->with('flash_notice', $flash_notice);
            }
        } elseif( Auth::user()->role == "Fellow" ) {
            if(is_null(Auth::user()->profile)){
                return Redirect::route('fellows.create')->with('flash_notice', $flash_notice);
            }
        } elseif( Auth::user()->role == "Hiring Manager" ) {
            //has a profile
            if(is_null(Auth::user()->profile)){
                return Redirect::route('hiringmanagers.create')->with('flash_notice', $flash_notice);
            } else {
                //profile is complete
                if(!Auth::user()->profile->isProfileComplete()){
                    return Redirect::route('hiringmanagers.edit', Auth::user()->profile->id)->with('flash_notice', $flash_notice);
                }
                //company profile is complete
                if(!Auth::user()->profile->company->isProfileComplete()){
                    return Redirect::route('companies.edit', Auth::user()->profile->company->id)->with('flash_notice', "Next, fill us in on your awesome copmany!");
                }
                //company has at least one published opportunity
                if(!Auth::user()->profile->company->hasPublishedOpportunities()){
                    return Redirect::route('opportunities.create')->with('flash_notice', "Finally, create at least one open Opportunity at your Company to gain access to the VFA Fellows.");
                }
            }
        } else {
            throw new Exception("Invalid User role!");
        }
    } else {
        throw new Exception("This route should require authentication");
    }
});

/*
|--------------------------------------------------------------------------
| Admin Filter
|--------------------------------------------------------------------------
|
| The Profile Filter ensures that the user is an admin before hitting 
| protected routes.
|
*/

Route::filter('admin', function()
{
    if(Auth::check()){
        if( Auth::user()->role != "Admin" ) {
            return Redirect::route('dashboard')->with('flash_error', "You don't have the necessary permissions to do that!");
        }
    } else {
        throw new Exception("This route should require authentication");
    }
});

/*
|--------------------------------------------------------------------------
| AdminOrFellow Filter
|--------------------------------------------------------------------------
|
| The Profile Filter ensures that the user is an admin or a fellow before hitting 
| protected routes.
|
*/

Route::filter('adminOrFellow', function()
{
    if(Auth::check()){
        if( !(Auth::user()->role == "Admin" || Auth::user()->role == "Fellow") ) {
            return Redirect::route('dashboard')->with('flash_error', "You don't have the necessary permissions to do that!");
        }
    } else {
        throw new Exception("This route should require authentication");
    }
});

/*
|--------------------------------------------------------------------------
| AdminOrHiringManager Filter
|--------------------------------------------------------------------------
|
| The Profile Filter ensures that the user is an admin or a hiring manager before hitting 
| protected routes.
|
*/

Route::filter('adminOrHiringManager', function()
{
    if(Auth::check()){
        if( !(Auth::user()->role == "Admin" || Auth::user()->role == "Hiring Manager") ) {
            return Redirect::route('dashboard')->with('flash_error', "You don't have the necessary permissions to do that!");
        }
    } else {
        throw new Exception("This route should require authentication");
    }
});