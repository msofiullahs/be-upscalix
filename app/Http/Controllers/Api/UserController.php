<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use DateTimeZone;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paginate = 25;
        if ($request->has('page_items')) {
            $paginate = $request->page_items;
        }
        $users = User::paginate($paginate);
        return response()->json($users, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|string|unique:users|max:255|email:rfc,dns',
            'birthday'      => 'required|date_format:Y-m-d',
            'location'      => 'required|timezone'
        ], [
            'location.timezone' => 'The location field must be a valid timezone. Please refer to this url '.url('api/location')
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $user = new User();
        $user->name = $request->first_name.' '.$request->last_name;
        $user->email = $request->email;
        $user->save();

        $profile = new UserProfile();
        $profile->user_id = $user->id;
        $profile->first_name = $request->first_name;
        $profile->last_name = $request->last_name;
        $profile->location = $request->location;
        $profile->birthday = $request->birthday;
        $profile->save();

        $result = $user->load('profile');
        return response()->json($result, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user)
    {
        $user = User::findOrFail($user);
        $result = $user->load('profile');
        return response()->json($user, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $user)
    {
        $validation = Validator::make($request->all(), [
            'first_name'    => 'string|max:255',
            'last_name'     => 'string|max:255',
            'email'         => 'string|unique:users|max:255|email:rfc,dns',
            'birthday'      => 'date_format:Y-m-d',
            'location'      => 'timezone'
        ], [
            'location.timezone' => 'The location field must be a valid timezone. Please refer to this url '.url('api/location')
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $userData = User::find($user);
        $profile = UserProfile::where('user_id', $userData->id)->first();

        $name = $userData->name;
        $email = $userData->email;
        $firstName = $profile->first_name;
        $lastName = $profile->last_name;
        $location = $profile->location;
        $birthday = $profile->birthday;

        if ($request->has('first_name') && $request->first_name != $firstName) {
            $splitName = explode(' ', $name);
            $name = $request->first_name.' '.$splitName[1];
            $firstName = $request->first_name;
        }

        if ($request->has('last_name') && $request->last_name != $lastName) {
            $splitName = explode(' ', $name);
            $name = $splitName[0].' '.$request->last_name;
            $lastName = $request->last_name;
        }

        if ($request->has('email') && $request->email != $email) {
            $email = $request->email;
        }

        if ($request->has('location') && $request->location != $location) {
            $location = $request->location;
        }

        if ($request->has('birthday') && $request->birthday != $birthday) {
            $birthday = $request->birthday;
        }

        $userData->name = $name;
        $userData->email = $email;
        $userData->save();

        $profile->first_name = $firstName;
        $profile->last_name = $lastName;
        $profile->location = $location;
        $profile->birthday = $birthday;
        $profile->save();

        $result = $userData->load('profile');
        return response()->json($result, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user)
    {
        $userData = User::find($user);
        $userData->profile()->delete();
        $userData->delete();
        return response()->json(['message'=>'ok'], 200);
    }

    /**
     * timezone location references
     */
    public function locations()
    {
        $tzList = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $mailApi = Http::post('https://email-service.digitalenvision.com.au/send-email', [
            'email'=> "sofiullah.work@gmail.com",
            'message'=> "Hey, Muhammad it's your birthday."
        ]);
        dd($mailApi->status());
        return response()->json($tzList, 200);
    }
}
