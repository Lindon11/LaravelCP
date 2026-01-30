@extends('installer.layout')

@section('title', 'Database Configuration')

@section('content')
<div class="p-12">
    <h2 class="text-3xl font-bold text-white mb-6">Database Configuration</h2>
    
    <p class="text-gray-300 mb-8">
        Enter your database details. Make sure the database already exists.
    </p>

    @if($errors->any())
        <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('installer.database.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-white font-semibold mb-2">Database Host</label>
                <input type="text" name="db_host" value="{{ old('db_host', 'localhost') }}" 
                       class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                       required>
            </div>

            <div>
                <label class="block text-white font-semibold mb-2">Database Port</label>
                <input type="number" name="db_port" value="{{ old('db_port', '3306') }}" 
                       class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                       required>
            </div>
        </div>

        <div>
            <label class="block text-white font-semibold mb-2">Database Name</label>
            <input type="text" name="db_name" value="{{ old('db_name') }}" 
                   placeholder="gangster_legends"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                   required>
            <p class="text-gray-400 text-sm mt-1">The database must already exist</p>
        </div>

        <div>
            <label class="block text-white font-semibold mb-2">Database Username</label>
            <input type="text" name="db_username" value="{{ old('db_username') }}" 
                   placeholder="root"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                   required>
        </div>

        <div>
            <label class="block text-white font-semibold mb-2">Database Password</label>
            <input type="password" name="db_password" value="{{ old('db_password') }}" 
                   placeholder="Leave empty if no password"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500">
        </div>

        <div class="flex justify-between pt-6 border-t border-white/10">
            <a href="{{ route('installer.requirements') }}" 
               class="text-gray-400 hover:text-white transition duration-200">
                ← Back
            </a>
            
            <button type="submit" 
                    class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
                Test Connection & Continue →
            </button>
        </div>
    </form>
</div>
@endsection
