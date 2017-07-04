<template>

<div>


<!-- Main container -->
<nav class="level">
  <!-- Left side -->
  <div class="level-left">
    <div class="level-item">
      <p class="subtitle is-5">
        <strong>{{ totalCount }}</strong> emails
      </p>
    </div>
    <div class="level-item">
      <div class="field has-addons">
        <p class="control">
          <input class="input" type="text" placeholder="Start with..." v-model="emailFiltered" v-on:input="updateValue($event.target.value)">
        </p>
      </div>
    </div>
  </div>

  <!-- Right side -->
  <div class="level-right">
    <p class="level-item"><strong>All</strong></p>
    <p class="level-item"><a>Unread</a></p>
    <p class="level-item"><a>Favorited</a></p>
  </div>
</nav>

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


  </div>
</template>

<script>

    import moment from 'moment';

    export default {

        data() {
          return {
            emails: [],
            totalCount: null,
            emailFiltered: null
          }
        },

        filters: {
          ago(date) {
            return moment(date.date).fromNow();
          }

        },

        mounted() {
            axios.get('/api/v1/emails').then(function(response) {
              this.emails = response.data.data
              this.totalCount = response.data.paginator.total_count
            }.bind(this));
        },

        methods: {
          updateValue(value) {
            axios.get('/api/v1/emails?search=' + value).then(function(response) {
              this.emails = response.data.data
              this.totalCount = response.data.paginator.total_count
            }.bind(this));
          }
        }
    }
</script>
