<template>
  <ResourceManager
    resourceName="Lottery"
    endpoint="/admin/casino/lotteries"
    :columns="columns"
    :defaultItem="defaultItem"
  >
    <template #form="{ item }">
      <div class="form-grid">
        <div class="form-group full-width">
          <label>Lottery Name *</label>
          <input v-model="item.name" type="text" required placeholder="e.g. Daily Jackpot">
        </div>

        <div class="form-group">
          <label>Ticket Price *</label>
          <input v-model.number="item.ticket_price" type="number" required min="1">
        </div>

        <div class="form-group">
          <label>Max Tickets</label>
          <input v-model.number="item.max_tickets" type="number" min="1">
        </div>

        <div class="form-group">
          <label>Prize Pool</label>
          <input v-model.number="item.prize_pool" type="number" min="0" readonly>
          <small>Auto-calculated from ticket sales</small>
        </div>

        <div class="form-group full-width">
          <label>Draw Date *</label>
          <input v-model="item.draw_date" type="datetime-local" required>
        </div>

        <div class="form-group">
          <label>Status *</label>
          <select v-model="item.status" required>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
            <option value="drawn">Drawn</option>
          </select>
        </div>
      </div>
    </template>
  </ResourceManager>
</template>

<script setup>
import ResourceManager from '@/components/ResourceManager.vue'

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'ticket_price', label: 'Ticket Price', format: 'currency' },
  { key: 'tickets_sold', label: 'Sold' },
  { key: 'max_tickets', label: 'Max' },
  { key: 'prize_pool', label: 'Prize Pool', format: 'currency' },
  { key: 'status', label: 'Status' },
  { key: 'draw_date', label: 'Draw Date', format: 'datetime' }
]

const defaultItem = {
  name: '',
  ticket_price: 1000,
  max_tickets: 1000,
  prize_pool: 0,
  tickets_sold: 0,
  draw_date: new Date().toISOString().slice(0, 16),
  winner_id: null,
  status: 'open'
}
</script>
