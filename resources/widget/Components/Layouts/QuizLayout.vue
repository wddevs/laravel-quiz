<script setup>
    import Footer from '../../Components/Quiz/Footer.vue'
    import Sidebar from '../../Components/Quiz/Sidebar.vue'
</script>

<template>
    <!-- Питання з сайдбаром-->
    <div class="quiz__layout">
        <div class="quiz__main">
        <slot />
        </div>

        <Sidebar />
    </div>

    <Footer />
</template>
