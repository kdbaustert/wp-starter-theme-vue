<script setup>
import { ref } from 'vue'

defineProps({
  title: {
    type: String,
    default: 'Vue island',
  },
})

const count = ref(0)
</script>

<template>
  <div class="card">
    <div class="card-body">
      <h2 class="card-title h5">{{ title }}</h2>
      <p class="card-text">
        Rendered by Vue, styled by Bootstrap.
      </p>
      <button type="button" class="btn btn-primary" @click="count++">
        Clicked <span class="badge text-bg-light">{{ count }}</span>
      </button>
    </div>
  </div>
</template>
