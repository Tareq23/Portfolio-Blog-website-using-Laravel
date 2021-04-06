<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailVerifiedTokenModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\ResetPasswordModel;
class MailController extends Controller
{
    public function confirmEmail($token)
    {   
        $token = EmailVerifiedTokenModel::where('token','=',$token)->first();
        $token->user()->update(['verified' => 1]);
        $roleId = RoleModel::select(['id'])->where('name','=','subscriber')->first();
        $userId = $token->user_id;
        $user = UserModel::find($userId);
        // $user->profile()->create([
        //     'name' => $user->name,
        //     'email' => $user->email,
        //     'image' => 'images/default/user.png',
        // ]);
        $user->roles()->attach($roleId);
        return redirect('/blog');
    }
    public function resetPassword($token,$email)
    {
        $result = ResetPasswordModel::where('token','=',$token)->count();
        if($result)
        {
            return view('blog',['reset_password'=>true,'email'=>$email]);
        }
    }
}
