<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\Tymon;
use App\Mail\RecoverPassword;
use App\Mail\RecoverUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function login(Request $request)
    {

        $credentials = $request->only('username', 'password');
        $v = Validator::make($credentials, [
            'username' => 'required|string',
            'password' => 'required'
        ]);
        if ($v->fails()) return $this->errorResponse($v->errors()->first(), 400);

        $user = User::where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])->first();

        if (!$user) {
            return $this->errorResponse('El usuario no existe', 400);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Las credenciales ingresadas no son validas'], 400);
        }

        ActivityLogController::createActivityLog(
            'El usuario ' . $user->username . ' inició sesión en el sistema.',
            'login');

        return [
            'access_token' => $token,
        ];
    }

    function getUsers()
    {
        return User::all();
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json([
            'user' => $user,
        ]);
    }

    public function generateCode()
    {
        $val = strtolower(Str::random(4));
        $userCode = User::where('verification_code', $val)->exists();
        if ($userCode) {
            $this->generateCode();
        } else {
            return $val;
        }
    }

    public function recoverPassword(Request $request)
    {
        try {
            //Validacion de la info
            $request = $request->all();
            $v = Validator::make($request, [
                'username_mail' => 'required|string'
            ]);

            if ($v->fails()) return $this->errorResponse($v->errors()->first(), 400);

            //Consultar si existe la info

            $user = User::where('username', $request['username_mail'])->first();
            $email = User::where('email', $request['username_mail'])->first();

            $idUser = null;
            if (isset($user)) {
                $idUser = $user->id;
            }

            if (isset($email)) {
                $idUser = $email->id;
            }

            if (is_null($idUser)) return $this->errorResponse('Los datos ingresados no estan en nuestros registros', 400);

            //Si pasa la validacion y existe, se asigna un codigo de verificacion y se envia un correo

            return DB::transaction(function () use ($idUser) {
                $verificationCode = $this->generateCode();
                $fecha = Carbon::now();
                $model = User::findOrFail($idUser);

                $model->verification_code = $verificationCode;
                $model->date_verification_code = $fecha->format('Y-m-d H:i:s');
                $model->save();

                $emailUser = User::select('email')->where('id', $idUser)->first();

                if (empty($emailUser->email)) {
                    return $this->errorResponse('El usuario no tiene un correo registrado, por favor contacte al administrador', 400);
                } else {
                    $email['email'] = $emailUser->email;
                    $email['subject'] = "Hola $model->username, se ha recibido satisfactorimente su solicitud";
                    $email['username'] = $model->username;
                    $email['code'] = $verificationCode;

                    Mail::to($email['email'])->send(new RecoverPassword($email));
                }
                return $this->susccesResponse('Correo de recuperacion enviado correctamente');

            }, 5);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function register(Request $request)
    {
        $user = new User;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
    }

    public function changePassword(Request $request)
    {
        try {
            $request = $request->all();

            $v = Validator::make($request, [
                'username' => 'required|exists:users,username',
                'password' => 'required|min:8',
                'verify_password' => 'required|min:8'
            ]);
            if ($v->fails()) return $this->errorResponse($v->errors()->first(), 400);

            if ($request['password'] !== $request['verify_password']) return response()->json(['mensaje' => 'La contraseña no coincide'], 400);

            return DB::transaction(function () use ($request) {
                $user = User::where('username', $request['username'])->first();
                $dataUser = User::findOrFail($user->id);
                $dataUser->verification_code = null;
                $dataUser->date_verification_code = null;
                $dataUser->password = bcrypt($request['password']);
                $dataUser->save();

                return response()->json(['mensaje' => 'Se modifico correctamente la contraseña']);

            }, 5);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function validateCode(Request $request)
    {
        try {
            $codigo = $request->all();

            $v = Validator::make($codigo, [
                'codigo_verificacion' => 'required|exists:users,verification_code',
            ]);
            if ($v->fails()) return $this->errorResponse($v->errors()->first(), 400);

            return DB::transaction(function () use ($codigo) {
                $user = User::select('id', 'username', 'date_verification_code')->where('verification_code', $codigo['codigo_verificacion'])->first();
                $fechaCodigo = Carbon::parse($user->fecha_cod_verificacion);
                $tiempoTranscurrido = Carbon::now()->diffInMinutes($fechaCodigo);
                if ($tiempoTranscurrido > 60) {
                    $usuario = User::findOrFail($user->id);
                    $usuario->verification_code = null;
                    $usuario->date_verification_code = null;
                    $usuario->save();
                    return $this->errorResponse('El codigo de verificacion ha caducado', 401);
                } else {
                    $data['user'] = $user->username;
                    return response()->json([
                        'mensaje' => 'Se verifico correctamente el codigo'
                    ]);
                }
            }, 5);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function recoverUser(Request $request)
    {
        $request = $request->all();
        $v = Validator::make($request, [
            'email' => 'required|email|exists:users,email'
        ]);
        if ($v->fails()) return $this->errorResponse($v->errors()->first(), 400);


        return DB::transaction(function () use ($request) {
            $verificationCode = $this->generateCode();
            $fecha = Carbon::now();
            $user = User::where('email', $request['email'])->first();
            $user->verification_code = $verificationCode;
            $user->date_verification_code = $fecha->format('Y-m-d H:i:s');
            $user->save();

            if (empty($user->email)) {
                return $this->errorResponse('El usuario no tiene un correo registrado, por favor contacte con el administrador', 400);
            } else {
                $email['email'] = $request['email'];
                $email['subject'] = "Hola $user->username, se ha recibido satisfactorimente su solicitud";
                $email['code'] = $verificationCode;

                Mail::to($email['email'])->send(new RecoverUser($email));
            }
            return $this->susccesResponse(['message' => 'Correo de recuperacion enviado correctamente']);
        }, 5);
    }

    function getUser($id)
    {
        return User::findOrFail($id);
    }

    function removeUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }

    function updateUser($id, Request $request)
    {
        $user = User::findOrFail($id);
        $user->email = $request->input('email');
        $user->username = $request->input('username');
        $user->password = bcrypt($request->input('password'));
        $user->save();
    }

    public function logout()
    {
        Log::info(JWTAuth::getToken());
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'mensaje' => 'Desconectado exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            throw $th;
        }

    }
}
