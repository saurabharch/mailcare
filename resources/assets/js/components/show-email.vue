<template>
  <div class="card" v-if="email">
    <div class="card-content">
      <nav class="level">
        <div class="level-left">
          <div>
            <h1 class="title is-spaced">{{ email.subject }}</h1>
            <h2 class="subtitle is-5" v-if="email.created_at">From <a>{{ email.from }}</a> to <a>{{ email.to }}</a></h2>
          </div>
        </div>
        <div class="level-right has-text-centered">
          <div>
            <p class="heading">{{ email.created_at | ago }}</p>
            <p class="title">
              <button :class="classes" @click='toggle'>
                <span class="icon is-small"><i class="fa fa-heart"></i></span>
              </button>
            </p>
          </div>
        </div>
      </nav>
      <hr>
      <show-email-body :id="id" :email="email"></show-email-body>
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

        computed: {
          classes() {
            console.log(this.email.favorite)
            return ['button', this.email.favorite ? 'is-primary' : '']
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

        methods: {
          toggle() {
            if (this.email.favorite) {
              axios.delete('/api/v1/emails/' + this.id + '/favorite')
              this.email.favorite = false;
            } else {
              axios.post('/api/v1/emails/' + this.id + '/favorite')
              this.email.favorite = true;

            }
          }
        }
    }

</script>
