<?php

namespace App\Http\Controllers;

use App\Mail\confirmation;
use App\Mail\Disabled;
use App\Mail\Enabled;
use App\Mail\Twofactor;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return Task::orderBy('id', 'desc')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',

        ]);

        $task = new Task;
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->save();

        if ($task) {
            return response()->json([
                'message' => 'Task was successfully created.',
                'data' => $task,
                'error' => false,
            ], 201);
        } else {
            return response()->json([
                'message' => 'An error occurred while creating the task.',
                'data' => null,
                'error' => true,
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return Task::findorFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
        ]);

        $task = Task::findOrFail($id);
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->save();

        return response()->json([
            'message' => 'Task was successfully updated.',
            'data' => $task,
            'error' => false,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        if ($task->delete()) {
            return 'Deleted Successfully';
        }
    }

    public function Createaccount(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }

        $data['password'] = Hash::make($request->password);
        $data['verification_code'] = rand(11111, 99999);
        $newuser = User::create($data);
        $token = $newuser->createToken('access-token')->accessToken;
        Mail::send(new confirmation($newuser));
        // Mail::send(new registration($newUser));
        return response()->json([
            'message' => 'User was successfully created.',
            'token' => $token,
            'data' => $newuser,
            'error' => false,
        ], 200);
    }

    public function getUser(Request $request)
    {
        $authUser = auth()->user();

        return response()->json([
            'message' => 'User was successfully retrieved.',
            'data' => $authUser,
        ]);
    }

    public function getallUser(Request $request)
    {
        return User::orderBy('created_at', 'desc')->get();
    }

    public function Login(Request $request)
    {
        $input = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $check_details = Auth::attempt($input);

        if ($check_details) {
            $user = User::where('email', $request->email)->first();
            $user->verification_code = rand(11111, 99999);
            $token = $user->createToken('access-token')->accessToken;
            $user->save();

            return response()->json([
                'message' => 'User login success',
                'token' => $token,
                'data' => $user,
                'error' => false,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Kindly enter the correct details',
                'data' => null,
                'error' => true,
            ], 401);
        }
    }

    public function Logout(Request $request)
    {
        auth()->user()->token()->revoke();

        return response()->json([
            'message' => 'User was logged out successfully',
        ]);
    }

    public function notLoggedin()
    {
        return response()->json([
            'message' => 'Login to proceed',
            'data' => null,
            'error' => true,
        ], 401);
    }

    public function resendCode(Request $request)
    {
        try {
            $input = $request->validate([
                'email' => 'required',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }

        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'You have already verified your email',
            ]);
        }

        $user_id = auth()->id();
        $user = User::where('id', $user_id)->first();
        $user->verification_code = rand(10000, 99999);
        $user->email = $input['email'];
        $user->save();
        Mail::send(new confirmation($user));

        return $user;
    }

    public function verifyCode(Request $request)
    {
        try {
            $input = $request->validate([
                'verification_code' => 'required',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }

        $user_verification_code = auth()->user()->verification_code;

        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'You have already verified your email',
            ], 403);
        }

        if ($input['verification_code'] == $user_verification_code) {
            if ($request->user()->markEmailAsVerified()) {
                event(new Verified($request->user()));
            }

            return response()->json([
                'message' => 'You have succesfully verified your email',
            ], 200);
        }
    }

    public function ToggleTwoFactor()
    {
        $user = User::where('id', auth()->id())->first();
        $user->two_factor_status = ! $user->two_factor_status;
        $user->save();
        $user->two_factor_status ? Mail::send(new Enabled($user)) : Mail::send(new Disabled($user));
        $msg = $user->two_factor_status ? 'Two factor enabled' : 'Two factor disabled';

        return response()->json([
            'message' => $msg,
            'data' => null,
            'error' => false,
        ]);
    }

    public function SendTwoFactor()
    {
        $user = User::where('id', auth()->id())->first();

        if (! $user->two_factor_status) {
            return response()->json([
                'message' => 'You have disabled two-factor authentication',
                'data' => null,
                'error' => true,
            ], 400);
        }

        $user->two_factor_code = rand(11111, 88888);
        $user->save();
        Mail::send(new Twofactor($user));

        return response()->json([
            'message' => 'Two-factor code sent successfully',
        ], 200);
    }
}