<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AuthorizationRequest;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response as Psr7Response;

class AuthorizationsController extends Controller
{


    public function store(AuthorizationRequest $request, AuthorizationServer $server, ServerRequestInterface $serverRequest)
    {
        // jwt auth
//        $username = $request->username;
//
//        filter_var($username, FILTER_VALIDATE_EMAIL) ?
//            $credentials['email'] = $username :
//            $credentials['phone'] = $username;
//
//        $credentials['password'] = $request->password;
//
//        if (!$token = Auth::guard('api')->attempt($credentials)) {
//            return $this->response->errorUnauthorized(trans('auth.failed'));
//        }
//
//        return $this->respondWithToken($token)->setStatusCode(201);


        // passport

        //respondToAccessTokenRequest 会依次处理：
        //检测 client 参数是否正确；
        //检测 scope 参数是否正确；
        //通过用户名查找用户；
        //验证用户密码是否正确；
        //生成 Response 并返回；
        try {
            return $server->respondToAccessTokenRequest($serverRequest, new Psr7Response)->withStatus(201);
        } catch (OAuthServerException $e) {
            return $this->response->errorUnauthorized($e->getMessage());
        }

    }


    public function socialStore($type, SocialAuthorizationRequest $request)
    {
        if (!in_array($type,['weixin'])) {
            return $this->response->errorBadRequest();
        }

        $driver = Socialite::driver($type);

        try {
            if ($code = $request->code) {
                $response = $driver->getAccessTokenResponse($code);
                $token = array_get($response, 'access_token');
            } else {
                $token = $request->access_token;

                if ($type == 'weixin') {
                    $driver->setOpenId($request->openid);
                }
            }

            $oauthUser = $driver->userFromToken($token);
        } catch (\Exception $e) {
            return $this->response->errorUnauthorized('参数错误，未获取用户信息');
        }

        switch ($type) {
            case 'weixin' :
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;
                if ($unionid) {
                    $user = User::where('weixin_unionid', $unionid)->first();
                } else {
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }

                //没有用户，默认创建一个用户
                if (!$user) {
                    $user = User::create([
                        'name' => $oauthUser->getNickname(),
                        'avatar' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid
                    ]);
                }
                break;
        }

        //第三方登录获取 user 后，我们可以使用 fromUser 方法为某一个用户模型生成token
        $token = Auth::guard('api')->formUser($user);
        return $this->respondWithToken($token)->setStatusCode(201);
    }

    public function update(AuthorizationServer $server, ServerRequestInterface $serverRequest)
    {
//        $token = Auth::guard('api')->refresh();
//        return $this->respondWithToken($token);

        try {
            return $server->respondToAccessTokenRequest($serverRequest, new Psr7Response);
        }catch (OAuthServerException $e) {
            return $this->response->errorUnauthorized($e->getMessage());
        }
    }

    public function destory()
    {
//        Auth::guard('api')->logout();
        $this->user()->token()->revoke();
        return $this->response->noContent();
    }


    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60 // JWT_TTL生成的token在多少分钟过期，默认60分钟
        ]);
    }
}
