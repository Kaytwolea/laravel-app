<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\confirmation;
use App\Models\Task;
use App\Models\User;
use Exception;
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
            'description' => 'required'

        ]);

        $task = new Task;
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->save();

        if ($task) {
            return response()->json([
                'message' => 'Task was successfully created.',
                'data' => $task,
                'error' => false
            ], 201);
        } else {
            return response()->json([
                'message' => 'An error occurred while creating the task.',
                'data' => null,
                'error' => true
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
            'description' => 'required'
        ]);

        $task = Task::findOrFail($id);
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->save();
        return response()->json([
            'message' => 'Task was successfully updated.',
            'data' => $task,
            'error' => false
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
            return "Deleted Successfully";
        }
    }


    public function Createaccount(Request $request)
    {

        try {
            $data = $request->validate([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
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
            'error' => false
        ], 200);
    }
    public function getUser(Request $request)
    {
        $authUser = auth()->user();
        return response()->json([
            'message' => 'User was successfully retrieved.',
            'data' => $authUser
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
            'password' => 'required'
        ]);

        $check_details = Auth::attempt($input);

        if ($check_details) {

            $user = User::where('email', $request->email)->first();
            $data['verification_code'] = rand(11111, 99999);
            Mail::send(new confirmation($user));
            return response()->json([
                'message' => 'Kindly check your mail and enter your verification to proceed',
                'data' => $user->verification_code,
                'error' => false
            ], 200);
        } else {
            return response()->json([
                'message' => 'Kindly enter the correct details',
                'data' => null,
                'error' => true
            ], 401);
        }
    }

    public function confirmCode(Request $request)
    {
        $code_input = $request->validate([
            'verification_code' => 'required'
        ]);
        if ($code_input) {
            $user = User::where('verification_code', $request->verification_code)->first();
            $token = $user->createToken('access-token')->accessToken;
            return response()->json([
                'message' => 'Verication code confirmed',
                'data' => $user,
                'token' => $token,
                'error' => false
            ], 200);
        } else {
            return response()->json([
                'message' => 'please enter a correct verification code',
                'data' => null,
                'error' => true
            ], 401);
        }
    }

    public function Logout(Request $request)
    {
        auth()->user()->token()->revoke();
        return response()->json([
            'message' => 'User was logged out successfully'
        ]);
    }

    public function Deleteuser($id)
    {
        $users = User::findOrFail($id);
        if ($users->delete()) {
            return response()->json([
                'message' => 'User was successfully deleted'
            ]);
        }
    }
}