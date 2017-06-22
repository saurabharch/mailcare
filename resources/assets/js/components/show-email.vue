<template>

<div class="card" v-if="email">

  <div class="card-content">
      <h1 class="title">{{ email.subject }}</h1>
      <h2 class="subtitle is-6" v-if="email.created_at">Email from {{ email.from }} to {{ email.to }} received {{ email.created_at | ago }}</h2>
      <hr>
  </div>
  <div class="card-content">
    <div class="content">
      <show-email-body :id="id" :email="email"></show-email-body>
    </div>
  </div>
</div>


</template>

<script>

    import moment from 'moment'

    export default {

        props: ['id'],

        data() {
          return {
            email: null,
          }
        },

        filters: {
          ago(value) {
            return moment(value.date).fromNow();
          }

        },

        mounted() {
            axios.get('/api/v1/emails/' + this.id).then(response => this.email = response.data.data);
        },
    }
</script>
