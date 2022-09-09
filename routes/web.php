<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "/api/v1"], function () use ($router) {
    $router->get("/wallets", [
        "uses" => "WalletController@list",
    ]);
    $router->get("/wallet/{id}", [
        "as" => "getWallet",
        "uses" => "WalletController@get",
    ]);
    $router->put("/wallet/{id}", [
        "as" => "putWallet",
        "uses" => "WalletController@edit",
        "validator" => "PutWallet",
    ]);
    $router->post("/wallet", [
        "as" => "createWallet",
        "uses" => "WalletController@create",
        "entity" => "WalletDto",
        "validator" => "PostWallet",
    ]);
    $router->post("/payin", [
        "uses" => "PayinController@create",
        "entity" => "PayinDto",
        "validator" => "PostPayin",
    ]);
    $router->get("/payins/{beneficiaryIban}", [
        "uses" => "PayinController@getByBeneficiary",
    ]);
    $router->post("/payout", [
        "uses" => "PayoutController@create",
        "entity" => "PayoutDto",
        "validator" => "PostPayout",
    ]);
    $router->get("/payouts", [
        "uses" => "PayoutController@getBy",
        "validator" => "GetPayoutBy",
    ]);
});
