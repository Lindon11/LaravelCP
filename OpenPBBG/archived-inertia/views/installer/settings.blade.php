@extends('installer.layout')

@section('title', 'Application Settings')

@section('content')
<div class="p-12">
    <h2 class="text-3xl font-bold text-white mb-6">Application Settings</h2>
    
    <p class="text-gray-300 mb-8">
        Configure your game's basic settings.
    </p>

    <form action="{{ route('installer.settings.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div>
            <label class="block text-white font-semibold mb-2">Application Name</label>
            <input type="text" name="app_name" value="{{ old('app_name', 'OpenPBBG') }}" 
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                   required>
            <p class="text-gray-400 text-sm mt-1">This will be displayed across your game</p>
        </div>

        <div>
            <label class="block text-white font-semibold mb-2">Application URL</label>
            <input type="url" name="app_url" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" 
                   placeholder="http://your-domain.com"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                   required>
            <p class="text-gray-400 text-sm mt-1">The URL where your game will be accessible</p>
        </div>

        <div>
            <label class="block text-white font-semibold mb-2">Environment</label>
            <select name="app_env" 
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500"
                    required>
                <option value="production" {{ old('app_env', 'production') == 'production' ? 'selected' : '' }}>
                    Production (Live Game)
                </option>
                <option value="local" {{ old('app_env') == 'local' ? 'selected' : '' }}>
                    Development (Testing)
                </option>
            </select>
            <p class="text-gray-400 text-sm mt-1">Choose Production for live games</p>
        </div>

        <div class="flex justify-between pt-6 border-t border-white/10">
            <a href="{{ route('installer.database') }}" 
               class="text-gray-400 hover:text-white transition duration-200">
                ← Back
            </a>
            
            <button type="submit" 
                    class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
                Continue →
            </button>
        </div>
    </form>
</div>
@endsection
