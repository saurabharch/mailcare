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
          <input class="input" type="text" placeholder="Start with..." v-model="emailFiltered" v-on:input="getEmails()">
        </p>
      </div>
    </div>
  </div>

  <!-- Right side -->
  <div class="level-right">

    <div class="tabs is-small is-toggle">
      <ul>
        <li :class="classes(!this.filteredBy)">
          <a @click="filterBy()">
            <span class="icon is-small"><i class="fa fa-asterisk"></i></span>
            <span>All</span>
          </a>
        </li>
        <li :class="classes(this.filteredByUnread())">
          <a @click="filterBy('unread')">
            <span class="icon is-small"><i class="fa fa-circle"></i></span>
            <span>Unread</span>
          </a>
        </li>
        <li :class="classes(this.filteredByFavorite())">
          <a @click="filterBy('favorite')">
            <span class="icon is-small"><i class="fa fa-heart"></i></span>
            <span>Favorite</span>
          </a>
        </li>
      </ul>
    </div>

  </div>
</nav>

    <table class="table table is-fullwidth">
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
            <span class="icon is-small">
              <i v-if="email.favorited" class="fa fa-star"></i>
              <i v-else-if="!email.read" class="fa fa-circle"></i>
            </span>
            <a v-bind:href="'/emails/' + email.id" v-bind:id="email.id">{{ email.subject }}</a>
          </td>
          <td>{{ email.from }}</td>
          <td>{{ email.to }}</td>
        </tr>
      </tbody>
    </table>
    <div>
    </div>
    <div>
    </div>

  </div>
</template>

<script>

    import moment from 'moment';

    export default {

        data() {
          return {
            emails: [],
            totalCount: null,
            emailFiltered: null,
            filteredBy: null,
          }
        },

        filters: {
          ago(date) {
            return moment(date.date).fromNow();
          }
        },

        mounted() {
          this.getEmails()
        },

        methods: {

          filteredByUnread() {
            return this.filteredBy == 'unread';
          },

          filteredByFavorite() {
            return this.filteredBy == 'favorite';
          },

          classes(filtered) {
            return [filtered ? 'is-active' : ''];
          },

          filterBy(filtered = null) {
            this.filteredBy = filtered
            this.getEmails()
          },

          getEmails() {
            axios.get('/api/v1/emails', { params: {
              'search': (this.emailFiltered ? this.emailFiltered : null),
              'unread': (this.filteredByUnread() ? '1' : null),
              'favorite': (this.filteredByFavorite() ? '1' : null),
            }}).then(function(response) {
              this.emails = response.data.data
              this.totalCount = response.data.paginator.total_count
            }.bind(this));

          },
        }
    }
</script>
