<template>
    <table class="table">
      <thead>
        <tr>
          <th><abbr title="Received date">Date</abbr></th>
          <th>Subject</th>
          <th>From</th>
          <th>To</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="email in emails">
          <td>{{ email.created_at | ago }}</td>
          <td>
            <a v-bind:href="'/emails/' + email.id" v-bind:id="email.id">{{ email.subject }}</a>
          </td>
          <td>{{ email.from }}</td>
          <td>{{ email.to }}</td>
        </tr>
      </tbody>
    </table>
</template>

<script>

    import moment from 'moment';

    export default {

        data() {
          return {
            emails: []
          }
        },

        filters: {
          ago(date) {
            return moment(date.date).fromNow();
          }

        },

        mounted() {
            axios.get('/api/v1/emails').then(response => this.emails = response.data.data);
        },

        methods: {
        }
    }
</script>
