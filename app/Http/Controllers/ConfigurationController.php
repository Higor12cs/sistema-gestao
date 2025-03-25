<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConfigurationController extends Controller
{
    public function index()
    {
        return Inertia::render('Configurations/Index', [
            'configurations' => Configuration::all(),
        ]);
    }

    public function edit(Configuration $configuration)
    {
        return Inertia::render('Configurations/Edit', [
            'configuration' => $configuration,
        ]);
    }

    public function update(Request $request, Configuration $configuration)
    {
        $configuration->update($request->all());

        return redirect()->route('configurations.index');
    }
}
