<template>
  <InstallerLayout current-step="complete" :hide-progress="true">
    <div class="p-12 text-center">
      <div class="text-6xl mb-6">🎉</div>
      
      <h2 class="text-4xl font-bold text-white mb-4">Installation Complete!</h2>
      
      <p class="text-gray-300 text-lg mb-8 max-w-2xl mx-auto">
        Your Gangster Legends installation is now complete and ready to use.
      </p>

      <div class="bg-green-500/20 border border-green-500 rounded-xl p-6 mb-8 max-w-2xl mx-auto">
        <h3 class="text-xl font-semibold text-white mb-4">What's Next?</h3>
        <ul class="space-y-3 text-gray-300 text-left">
          <li class="flex items-start">
            <span class="text-green-400 mr-3 mt-1">→</span>
            <span>Log in with your admin credentials</span>
          </li>
          <li class="flex items-start">
            <span class="text-green-400 mr-3 mt-1">→</span>
            <span>Configure your game settings in the admin panel</span>
          </li>
          <li class="flex items-start">
            <span class="text-green-400 mr-3 mt-1">→</span>
            <span>Enable and configure modules</span>
          </li>
          <li class="flex items-start">
            <span class="text-green-400 mr-3 mt-1">→</span>
            <span>Customize your game content</span>
          </li>
        </ul>
      </div>

      <div class="bg-white/5 rounded-xl p-6 mb-8 max-w-2xl mx-auto">
        <h3 class="text-xl font-semibold text-white mb-4">Important Security Notes</h3>
        <ul class="space-y-3 text-gray-300 text-left text-sm">
          <li class="flex items-start">
            <span class="text-yellow-400 mr-3 mt-1">⚠</span>
            <span>Make sure to secure your .env file and prevent public access</span>
          </li>
          <li class="flex items-start">
            <span class="text-yellow-400 mr-3 mt-1">⚠</span>
            <span>Change your admin password after first login</span>
          </li>
          <li class="flex items-start">
            <span class="text-yellow-400 mr-3 mt-1">⚠</span>
            <span>If running in production, ensure APP_ENV is set to 'production' and APP_DEBUG is 'false'</span>
          </li>
        </ul>
      </div>

      <div class="space-y-4">
        <a 
          href="/"
          class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
        >
          Go to Homepage
        </a>
        <br>
        <a 
          href="/admin/login"
          class="inline-block bg-gray-600 hover:bg-gray-700 text-white font-semibold px-8 py-4 rounded-lg transition duration-200"
        >
          Go to Admin Panel
        </a>
      </div>

      <div class="mt-8 text-gray-400 text-sm">
        <p>Thank you for choosing Gangster Legends!</p>
      </div>
    </div>
  </InstallerLayout>
</template>

<script setup>
import InstallerLayout from './InstallerLayout.vue';
</script>
