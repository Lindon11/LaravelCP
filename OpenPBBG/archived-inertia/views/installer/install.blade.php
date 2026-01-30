@extends('installer.layout')

@section('title', 'Installing')

@section('content')
<div class="p-12">
    <h2 class="text-3xl font-bold text-white mb-6">Installing Your Game</h2>
    
    <p class="text-gray-300 mb-8">
        Please wait while we set everything up. This may take a minute...
    </p>

    <div id="progress-container" class="space-y-4 mb-8">
        <div class="flex items-center space-x-3">
            <div class="spinner" id="spinner-1"></div>
            <span class="text-gray-300" id="step-1">Running database migrations...</span>
        </div>
        <div class="flex items-center space-x-3 opacity-50">
            <div class="spinner" id="spinner-2"></div>
            <span class="text-gray-300" id="step-2">Creating storage links...</span>
        </div>
        <div class="flex items-center space-x-3 opacity-50">
            <div class="spinner" id="spinner-3"></div>
            <span class="text-gray-300" id="step-3">Finalizing installation...</span>
        </div>
    </div>

    <div id="error-message" class="hidden bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
        <strong>Error:</strong> <span id="error-text"></span>
    </div>

    <div id="success-message" class="hidden">
        <div class="text-center">
            <div class="text-6xl mb-4">✓</div>
            <h3 class="text-2xl font-bold text-white mb-4">Installation Successful!</h3>
            <p class="text-gray-300 mb-6">Your game engine has been installed successfully.</p>
            <a href="{{ route('installer.admin') }}" 
               class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition duration-200">
                Create Admin Account →
            </a>
        </div>
    </div>
</div>

<style>
    .spinner {
        border: 3px solid rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        border-top: 3px solid #9333ea;
        width: 24px;
        height: 24px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 0;
    const steps = ['spinner-1', 'spinner-2', 'spinner-3'];
    
    // Start installation
    fetch('{{ route('installer.install.process') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success after completing all steps
            setTimeout(() => {
                document.querySelectorAll('.spinner').forEach(s => s.classList.add('hidden'));
                document.getElementById('progress-container').classList.add('hidden');
                document.getElementById('success-message').classList.remove('hidden');
            }, 2000);
        } else {
            document.getElementById('error-message').classList.remove('hidden');
            document.getElementById('error-text').textContent = data.message;
            document.querySelectorAll('.spinner').forEach(s => s.classList.add('hidden'));
        }
    })
    .catch(error => {
        document.getElementById('error-message').classList.remove('hidden');
        document.getElementById('error-text').textContent = error.message;
        document.querySelectorAll('.spinner').forEach(s => s.classList.add('hidden'));
    });

    // Animate steps
    const animateSteps = setInterval(() => {
        if (currentStep < steps.length - 1) {
            currentStep++;
            document.getElementById(steps[currentStep]).parentElement.parentElement.classList.remove('opacity-50');
        } else {
            clearInterval(animateSteps);
        }
    }, 1000);
});
</script>
@endsection
