<template>
  <ResourceManager
    resourceName="Stock"
    endpoint="/admin/stocks/stocks"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group">
          <label>Symbol *</label>
          <input v-model="item.symbol" type="text" required placeholder="e.g. TECH" maxlength="5" style="text-transform: uppercase">
        </div>

        <div class="form-group">
          <label>Company Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. TechCorp Inc.">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Company description..."></textarea>
        </div>

        <div class="form-group">
          <label>Sector *</label>
          <select v-model="item.sector" required>
            <option value="Technology">Technology</option>
            <option value="Finance">Finance</option>
            <option value="Healthcare">Healthcare</option>
            <option value="Energy">Energy</option>
            <option value="Retail">Retail</option>
            <option value="Manufacturing">Manufacturing</option>
            <option value="Real Estate">Real Estate</option>
          </select>
        </div>

        <div class="form-group">
          <label>Current Price *</label>
          <input v-model.number="item.current_price" type="number" required min="0.01" step="0.01">
        </div>

        <div class="form-group">
          <label>Shares Available *</label>
          <input v-model.number="item.shares_available" type="number" required min="0">
        </div>

        <div class="form-group">
          <label>Volatility (%) *</label>
          <input v-model.number="item.volatility" type="number" required min="0" max="100" step="0.1">
        </div>

        <div class="form-group">
          <div class="checkbox-wrapper">
            <input v-model="item.is_active" type="checkbox" id="active-checkbox">
            <label for="active-checkbox">Active Trading</label>
          </div>
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'symbol', label: 'Symbol', sortable: true },
  { key: 'name', label: 'Name' },
  { key: 'sector', label: 'Sector' },
  { key: 'current_price', label: 'Price', format: 'currency' },
  { key: 'shares_available', label: 'Shares' },
  { key: 'volatility', label: 'Volatility %' },
  { key: 'is_active', label: 'Active', format: 'boolean' }
]

const defaultItem = {
  symbol: '',
  name: '',
  sector: 'Technology',
  description: '',
  current_price: 100.00,
  day_open: 100.00,
  day_high: 100.00,
  day_low: 100.00,
  shares_available: 100000,
  shares_traded: 0,
  market_cap: 10000000,
  volatility: 15.00,
  is_active: true
}
</script>
