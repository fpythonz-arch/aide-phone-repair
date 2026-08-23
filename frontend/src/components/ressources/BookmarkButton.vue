<template>
  <button 
    class="bookmark-btn"
    :class="{ bookmarked: isBookmarked }"
    @click.stop="$emit('toggle')"
  >
    <span v-if="isBookmarked">★</span>
    <span v-else>☆</span>
  </button>
</template>

<script setup lang="ts">
interface Props {
  isBookmarked: boolean
}

defineProps<Props>()
defineEmits<{
  toggle: []
}>()
</script>

<style scoped>
.bookmark-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: #f3f4f6;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.1rem;
  color: #9ca3af;
  transition: all 0.2s;
  flex-shrink: 0;
}

.bookmark-btn:hover {
  background: #fef3c7;
  color: #f59e0b;
}

.bookmark-btn.bookmarked {
  background: #fef3c7;
  color: #f59e0b;
}

.bookmark-btn.bookmarked:hover {
  background: #fee2e2;
  color: #ef4444;
}
</style>