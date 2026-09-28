<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:settings.view')->only(['index']);
        $this->middleware('can:settings.edit')->only(['store']);
    }

    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('settings.edit');
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = $request->except('_token');
        unset($data['business_logo']);
        unset($data['srb_pos_id'], $data['srb_pos_user'], $data['srb_pos_password']);

        if ($request->hasFile('business_logo')) {
            $existingLogo = config('settings.business_logo');
            if ($existingLogo) {
                Storage::disk('public')->delete($existingLogo);
            }

            $data['business_logo'] = $request->file('business_logo')->store('settings', 'public');
        }

        foreach ($data as $key => $value) {
            $setting = Setting::firstOrCreate(['key' => $key]);
            $setting->value = $value;
            $setting->save();
        }

        if (isset($data['srb_enabled']) && $data['srb_enabled'] == 1) {
            config(['srb.enabled' => true]);
        } else {
            config(['srb.enabled' => false]);
        }

        return redirect()->route('settings.index');
    }
}
