<template>
	<div>
		<nav class="level">
			<div class="level-left">
				<p class="level_item subtitle is-5">
					<strong v-text="automations.length"></strong> automations
				</p>
			</div>
			<div class="level-right">
				<a class="level-item button is-primary" 
				@click='toggleCreateForm' 
				v-if="showCreateForm">Close</a>
				<a class="level-item button is-primary" 
				@click='toggleCreateForm' 
				v-else>Create</a>
			</div>
		</nav>

		<automation 
			v-on:automation-created="automationCreated()"
			v-if="showCreateForm" 
			:data-open='true' 
			:data-editable='true'>
		</automation>
		<automation v-for="automation in automations" 
			v-on:automation-deleted="automationDeleted()"
			:key="automation.id" 
			:data-automation="automation"
			>
		</automation>
	</div>
</template>

<script>
	import automation from './automation.vue'

	export default {

		components: {
			automation,
		},

		data() {
			return {
				automations: [],
				showCreateForm: false,
			}
		},

		mounted() {

			this.getAutomations()
		},

		methods: {
			toggleCreateForm() {
				this.showCreateForm = !this.showCreateForm
			},

			automationCreated() {
				this.toggleCreateForm()
				this.getAutomations()

			},

			automationDeleted() {
				this.getAutomations()

			},

			getAutomations() {
				axios.get('/api/automations').then(function(response) {
					this.automations = response.data.data
				}.bind(this));
			}
		}

	}
</script>