@extends('installer.layout')

@section('title', 'Create Admin Account')

@section('content')
<div class="p-12">
    <h2 class="text-3xl font-bold text-white mb-6">Create Admin Account</h2>
    
    <p class="text-gray-300 mb-8">
        Create your administrator account to manage the game.
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

    <form action="{{ route('installer.admin.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div>
            <label class="block text-white font-semibold mb-2">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" 
                   placeholder="admin"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                   required>
        </div>

        <div>
            <label class="block text-white font-semibold mb-2">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" 
                   placeholder="admin@example.com"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                   required>
        </div>

        <div>
            <label class="block text-white font-semibold mb-2">Password</label>
            <input type="password" name="password" 
                   placeholder="Minimum 8 characters"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                   required>
        </div>

        <div>
            <label class="block text-white font-semibold mb-2">Confirm Password</label>
            <input type="password" name="password_confirmation" 
                   placeholder="Re-enter your password"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                   required>
        </div>

        <div class="flex justify-end pt-6 border-t border-white/10">
            <button type="submit" 
                    class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
                Create Account & Finish →
            </button>
        </div>
    </form>
</div>
@endsection
