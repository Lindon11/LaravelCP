<template>
  <ResourceManager
    resourceName="Casino Game"
    endpoint="/admin/casino/games"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Game Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. Blackjack">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Game description..."></textarea>
        </div>

        <div class="form-group">
          <label>Game Type *</label>
          <select v-model="item.game_type" required>
            <option value="slots">Slots</option>
            <option value="blackjack">Blackjack</option>
            <option value="roulette">Roulette</option>
            <option value="poker">Poker</option>
            <option value="dice">Dice</option>
            <option value="lottery">Lottery</option>
          </select>
        </div>

        <div class="form-group">
          <label>Min Bet *</label>
          <input v-model.number="item.min_bet" type="number" required min="1">
        </div>

        <div class="form-group">
          <label>Max Bet *</label>
          <input v-model.number="item.max_bet" type="number" required min="1">
        </div>

        <div class="form-group">
          <label>House Edge (%) *</label>
          <input v-model.number="item.house_edge" type="number" required min="0" max="100" step="0.1">
        </div>

        <div class="form-group">
          <label>Max Payout Multiplier</label>
          <input v-model.number="item.max_payout_multiplier" type="number" min="1" step="0.1">
        </div>

        <div class="form-group">
          <label>Image URL</label>
          <input v-model="item.image" type="text" placeholder="https://...">
        </div>

        <div class="form-group">
          <div class="checkbox-wrapper">
            <input v-model="item.is_active" type="checkbox" id="active-checkbox">
            <label for="active-checkbox">Active</label>
          </div>
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'game_type', label: 'Type' },
  { key: 'min_bet', label: 'Min Bet', format: 'currency' },
  { key: 'max_bet', label: 'Max Bet', format: 'currency' },
  { key: 'house_edge', label: 'House Edge %' },
  { key: 'is_active', label: 'Active', format: 'boolean' }
]

const defaultItem = {
  name: '',
  game_type: 'slots',
  description: '',
  image: '',
  min_bet: 100,
  max_bet: 10000,
  house_edge: 5.0,
  max_payout_multiplier: 100,
  rules: null,
  is_active: true
}
</script>
