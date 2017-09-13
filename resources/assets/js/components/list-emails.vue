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

    <div>
    <p>
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
    </p>
    <p v-if="this.inbox">
    Filtered by inbox:
    <span class="tag is-primary">
      name@company2.com
      <button @click="removeFilter()" class="delete is-small"></button>
    </span>
    </p>
    <p v-if="this.sender">
    Filtered by sender:
    <span class="tag is-primary">
      name@company2.com 
      <button @click="removeFilter()" class="delete is-small"></button>
    </span>
    </p>
    </div>
  </div>

</nav>

    <table class="table table is-fullwidth" v-if="totalCount">
      <thead>
        <tr>
          <th><abbr title="Received date">Date</abbr></th>
          <th>Subject</th>
          <th>Sender</th>
          <th>Inbox</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="email in emails">
          <td>{{ email.created_at | ago }}</td>
          <td>
            <span class="icon is-small">
              <i v-if="email.favorite" class="fa fa-heart"></i>
              <i v-else-if="!email.read" class="fa fa-circle"></i>
            </span>
            <a v-bind:href="'/emails/' + email.id" v-bind:id="email.id">{{ email.subject }}</a>
          </td>
          <td>{{ email.sender.email }}</td>
          <td>{{ email.inbox.email }}</td>
        </tr>
      </tbody>
    </table>


    <article class="message" v-else>
      <div class="message-header" style="justify-content: flex-start;">
        <span class="icon is-medium"><i class="fa fa-meh-o"></i></span>
        <span>Oops! No emails matched your query.</span>
      </div>
      <div class="message-body">
      <p>A few things that might help:</p>
      <p>- Use MailCare to receive your first email</p>
      <p>- Remove your query filter Start with...</p>
      <p>- Change your query filter Unread/Favorite to All</p>
      <p>- Remove your query filter Inbox/Sender</p>
      </div>
    </article>

    <div>
    </div>
    <div>
    </div>

  </div>
</template>

<script>

    import moment from 'moment';

    export default {

        props: ['inbox', 'sender'],

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
          console.log(this.emails)
        },

        methods: {

          removeFilter() {
            window.location = "/"
          },

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
              'inbox': (this.inbox ? this.inbox : null),
              'sender': (this.sender ? this.sender : null),
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
