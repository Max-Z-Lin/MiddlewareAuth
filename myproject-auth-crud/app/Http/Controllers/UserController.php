<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Repository\UserRepository;
use JWTAuth;
use phpDocumentor\Reflection\Types\Boolean;


class UserController extends Controller
{
    protected $userRepository;

    //Repository建構子
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    //登入驗證
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password'); //取出request的email及password
        $token = JWTAuth::attempt($credentials);    //透過JWTAuth::attempt來驗證email及password
        try {
            if (! $token) {    //如果驗證失敗，回傳顯示無效
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (\JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(['token' => $token]);
    }

    //註冊資料
    public function register(Request $request)
    {
        //驗證資料是否符合規範
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'gender' => 'boolean'
        ]);

        //如果輸入的資料不符合規範，則印出error message
        if($validator->fails()){
            return response()->json([
                'error message' => $validator->errors(),
                'erroe code' => 400]);
        }
        $user = $this->userRepository->createUser($request);

        //將使用者資料透過JWTAuth::fromUser產生token
        $token = JWTAuth::fromUser($user);


        return response()->json([
            'data' => $user,
            'token' => $token,
            'error code' => 201]);
    }

    //利用token讀取資料
    public function getAuthUser()
    {
        try {

            $data = $this->jwtAuth();

                return response()->json([
                    'data' => $data,
                    'massage' => 'success'
                ]);

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token absent'], $e->getStatusCode());

        }

    }

    //更新資料
    public function updateAuthUser(Request $request)
    {
        if ($this->jwtAuth()) {
            $data = $this->userRepository
                ->updateUser($request);
            return response()->json([
                'data' => $data,
                'message' => null]);
        } else {
            return response()->json([
                'data' => null,
                'message' => 'error']);
        }
    }

    //刪除資料
    public function softDelete(Request $request)
    {
        if ($this->jwtAuth()) {
            $data = $this->userRepository
                ->deleteUser($request);
            if($data == true){
                return response()->json([
                    'message' => 'has been deleted']);
            } else {
                return response()->json([
                    'message' => 'error']);
            }

        }
    }

    //jwt驗證
    public function jwtAuth()
    {
        if ($user = JWTAuth::parseToken()->authenticate()) {
            return $user;
        } else {
            return null;
        }
    }
}
