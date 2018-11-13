
<template>
	<div>
		<div class="card">
      <header class="card-header">
        <p class="card-header-title">
          {{ automation.title }}&#8239;
          <span class="has-text-grey has-text-weight-normal is-size-7">
            ({{ automation.emails_received }} emails received)
          </span>
        </p>
        <a href="#" class="card-header-icon" aria-label="more options" @click.prevent='toggleOpen'>
          <span class="icon">
            <i class="fas fa-angle-down" aria-hidden="true" v-if="!open"></i>
            <i class="fas fa-angle-up" aria-hidden="true" v-else="!open"></i>
          </span>
        </a>
      </header>
      <div class="card-content" v-if="open">
        <div class="content">
          <div class="columns">
            <div class="column">
              <div>
                <div class="field">
                  <label class="label">Title</label>
                  <div class="control">
                    <input class="input" type="text" placeholder="Text input" required 
                    v-model="automation.title" 
                    :disabled="!editable">
                  </div>
                </div>

                <div class="field">
                  <label class="label">Sender</label>
                  <div class="control has-icons-left has-icons-right">
                    <input class="input" placeholder="Email input" 
                    v-model="automation.sender" 
                    :disabled="!editable">
                    <span class="icon is-small is-left">
                      <i class="fas fa-envelope"></i>
                    </span>
                  </div>
                </div>

                <div class="field">
                  <label class="label">Inbox</label>
                  <div class="control has-icons-left has-icons-right">
                    <input class="input" placeholder="Email input" 
                    v-model="automation.inbox" 
                    :disabled="!editable">
                    <span class="icon is-small is-left">
                      <i class="fas fa-envelope"></i>
                    </span>
                  </div>
                </div>

                <div class="field">
                  <label class="label">Subject</label>
                  <div class="control">
                    <input class="input" type="text" placeholder="Text input" 
                    v-model="automation.subject" 
                    :disabled="!editable">
                  </div>
                </div>

                <div class="field">
                  <div class="control">
                    <label class="checkbox">
                      <input type="checkbox" 
                      v-model="automation.has_attachments" 
                      :disabled="!editable">
                      Has attachments
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="column">
              <div class="field">
                <label class="label">Url</label>
                <div class="control">
                  <input class="input" type="text" 
                  placeholder="https://example.com/trigger-email-received" 
                  required 
                  v-model="automation.action_url" 
                  :disabled="!editable">
                </div>
              </div>

              <div class="field">
                <label class="label">Secret Token</label>
                <div class="control">
                  <input class="input" type="text" placeholder="Text input" 
                  v-model="automation.action_secret_token" 
                  :disabled="!editable">
                </div>
                <p class="help">Use this token to validate received payloads. It will be sent with the request in the X-Mailcare-Token HTTP header.</p>
              </div>
            </div>
          </div>

          <div class="field is-grouped is-grouped-right" v-if="open && !automation.id">
            <p class="control">
              <a class="button is-link" @click='createAutomation'>
                Create
              </a>
            </p>
          </div>

          <div class="field is-grouped is-grouped-right" v-if="open && automation.id && editable">
            <p class="control">
              <a class="button is-link" @click='saveAutomation'>
                Save
              </a>
            </p>
            <p class="control">
              <a class="button is-danger" @click='deleteAutomation'>
                Delete
              </a>
            </p>
            <p class="control">
              <a class="button" @click='toggleEditable'>
                Cancel
              </a>
            </p>
          </div>

          <div class="field is-grouped is-grouped-right" v-if="open && automation.id && !editable">
            <p class="control">
              <a class="button is-link" @click='toggleEditable'>
                Edit
              </a>
            </p>
          </div>

        </div>
      </div>
    </div>
    <br>
  </div>
</template>

<script>
	export default {

    props: {
      dataAutomation: {
        type: Object,
        default: {
          title: 'New automation',
          sender: '',
          inbox: '',
          subject: '',
          has_attachments: false,
          action_url: '',
          action_secret_token: '',
          emails_received: 0,
        }
      },
      dataOpen: {
        type: Boolean,
        default: false,
      },
      dataEditable: {
        type: Boolean,
        default: false,
      },
    },

    data() {
      return {
        automation: this.dataAutomation,
        open: this.dataOpen,
        editable: this.dataEditable,
      }
    },

    methods: {

      toggleOpen() {
        this.open = !this.open
      },

      toggleEditable() {
        this.editable = !this.editable
      },

      createAutomation() {
        axios.post('/api/automations', this.automation).then((response) => {
          this.$emit('automation-created', this.automation)
        })
      },

      saveAutomation() {
        axios.put('/api/automations/' + this.automation.id, this.automation).then((response) => {
          this.editable = false
        })
      },

      deleteAutomation() {
        axios.delete('/api/automations/' + this.automation.id, this.automation).then((response) => {
          this.$emit('automation-deleted', this.automation)
        })
      }
    }

  }
</script>