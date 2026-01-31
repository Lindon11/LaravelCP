<template>
  <ResourceManager
    resourceName="Item"
    endpoint="/admin/items"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. Bulletproof Vest">
        </div>

        <div class="form-group full-width">
          <label>Description *</label>
          <textarea v-model="item.description" required rows="3" placeholder="Item description..."></textarea>
        </div>

        <div class="form-group">
          <label>Type *</label>
          <select v-model="item.type" required>
            <option value="weapon">Weapon</option>
            <option value="armor">Armor</option>
            <option value="consumable">Consumable</option>
            <option value="collectible">Collectible</option>
            <option value="misc">Miscellaneous</option>
          </select>
        </div>

        <div class="form-group">
          <label>Rarity *</label>
          <select v-model="item.rarity" required>
            <option value="common">Common</option>
            <option value="uncommon">Uncommon</option>
            <option value="rare">Rare</option>
            <option value="epic">Epic</option>
            <option value="legendary">Legendary</option>
          </select>
        </div>

        <div class="form-group">
          <label>Price *</label>
          <input v-model.number="item.price" type="number" min="0" required>
        </div>

        <div class="form-group">
          <label>Sell Price</label>
          <input v-model.number="item.sell_price" type="number" min="0">
        </div>

        <div class="form-group">
          <label>Max Stack</label>
          <input v-model.number="item.max_stack" type="number" min="1">
        </div>

        <div class="form-group">
          <label>Image URL</label>
          <input v-model="item.image" type="text" placeholder="https://...">
        </div>

        <div class="form-group">
          <div class="checkbox-wrapper">
            <input v-model="item.tradeable" type="checkbox" id="tradeable-checkbox">
            <label for="tradeable-checkbox">Tradeable</label>
          </div>
        </div>

        <div class="form-group">
          <div class="checkbox-wrapper">
            <input v-model="item.stackable" type="checkbox" id="stackable-checkbox">
            <label for="stackable-checkbox">Stackable</label>
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
  { key: 'type', label: 'Type' },
  { key: 'rarity', label: 'Rarity' },
  { key: 'price', label: 'Price', format: 'currency' },
  { key: 'tradeable', label: 'Tradeable', format: 'boolean' }
]

const defaultItem = {
  name: '',
  type: 'misc',
  description: '',
  image: '',
  price: 0,
  sell_price: 0,
  tradeable: true,
  stackable: false,
  max_stack: 1,
  rarity: 'common'
}
</script>

<style scoped>
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  padding: 0.875rem 1.125rem;
  border: 2px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.625rem;
  background: rgba(15, 23, 42, 0.5);
  color: #f1f5f9;
  font-size: 0.938rem;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
  resize: vertical;
  font-family: inherit;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0;
}

.checkbox-wrapper input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #3b82f6;
}

.checkbox-wrapper label {
  margin: 0;
  cursor: pointer;
  font-weight: 500;
  color: #cbd5e1;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
