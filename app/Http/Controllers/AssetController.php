<?php

namespace App\Http\Controllers;

use App\Models\Nation;
use App\Models\Rank;
use App\Models\User;
use App\Models\WeaponForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class AssetController extends Controller {
    //

    private function retrieveAsset(string $assetName) {
        /** 
         * @disregard Intelephense non rileva il metodo temporaryurl
         * 
         * @see https://github.com/spatie/laravel-google-cloud-storage
         */

        $url = Storage::disk('gcs')->temporaryUrl(
            $assetName,
            now()->addMinutes(5)
        );

        return $url;
    }

    public function logo() {
        $url = $this->retrieveAsset('logo.png');
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];
        return response($image, 200, $headers);
    }

    public function logoex() {
        $url = $this->retrieveAsset('/assets/logo_ex2.png');
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];
        return response($image, 200, $headers);
    }

    public function logoSaber() {
        $url = $this->retrieveAsset("/assets/saber.png");
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];
        return response($image, 200, $headers);
    }

    public function logoSaberK() {
        $url = $this->retrieveAsset("/assets/saber_K.png");
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];
        return response($image, 200, $headers);
    }

    public function warriors() {
        $url = $this->retrieveAsset("/assets/logo-home-bianco.png");
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];
        return response($image, 200, $headers);
    }

    public function spadaHome() {
        $url = $this->retrieveAsset("/assets/spada-home-2.png");
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];
        return response($image, 200, $headers);
    }

    public function bollino() {
        $url = $this->retrieveAsset("/assets/bollino.png");
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];
        return response($image, 200, $headers);
    }

    public function nationFlag(Nation $nation) {
        $url = $this->retrieveAsset($nation->flag);
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];
        return response($image, 200, $headers);
    }

    public function rankImage(Rank $rank) {
        $url = $this->retrieveAsset("/ranks/{$rank->id}/logo.png");
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($image),
        ];

        return response($image, 200, $headers);
    }

    public function weaponFormImage(WeaponForm $weapon) {

        $url = $this->retrieveAsset($weapon->image);
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/webp',
            'Content-Length' => strlen($image),
        ];

        return response($image, 200, $headers);
    }


    public function weaponFormFor(WeaponForm $weapon, User $user) {

        $asset_name = explode(' ', $weapon->name);
        $asset = $asset_name[0] . "_" . $asset_name[1] . ".svg";

        switch ($user->getRole()) {
            case "athlete":
                if ($user->weaponForms()->pluck('weapon_forms.id')->contains($weapon->id)) {
                    $url = $this->retrieveAsset("/weapon-forms/athlete/{$asset}");
                } else {
                    $url = $this->retrieveAsset("/weapon-forms/default/{$asset}");
                }

                break;
            case "instructor":
                if ($user->weaponFormsPersonnel()->pluck('weapon_forms.id')->contains($weapon->id)) {
                    $url = $this->retrieveAsset("/weapon-forms/instructor/{$asset}");
                } else {
                    $url = $this->retrieveAsset("/weapon-forms/default/{$asset}");
                }
                break;
            case "technician":
                if ($user->weaponFormsTechnician()->pluck('weapon_forms.id')->contains($weapon->id)) {
                    $url = $this->retrieveAsset("/weapon-forms/technician/{$asset}");
                } else {
                    $url = $this->retrieveAsset("/weapon-forms/default/{$asset}");
                }
                break;

            default:
                $url = $this->retrieveAsset("/weapon-forms/default/{$asset}");
                break;
        }



        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/svg+xml',
            'Content-Length' => strlen($image),
        ];

        return response($image, 200, $headers);
    }

    public function favicon() {
        $url = $this->retrieveAsset('/assets/favicon.ico');
        $response = Http::get($url);
        $image = $response->body();
        $headers = [
            'Content-Type' => 'image/x-icon',
            'Content-Length' => strlen($image),
        ];
        return response($image, 200, $headers);
    }
}
