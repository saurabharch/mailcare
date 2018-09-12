<template>

  <div>
    <nav class="level"> 
      <div class="level-item has-text-centered"> 
        <div> 
          <p class="heading">Emails received</p> 
          <p class="title" v-text="meta.emails_received"></p> 
        </div> 
      </div> 
      <div class="level-item has-text-centered"> 
        <div> 
          <p class="heading">Inboxes created</p> 
          <p class="title" v-text="meta.inboxes_created"></p> 
        </div> 
      </div> 
      <div class="level-item has-text-centered"> 
        <div> 
          <p class="heading">Storage used</p> 
          <p class="title" v-text="meta.storage_used_for_human"></p> 
        </div> 
      </div> 
    </nav> 


<div class="container">
  <canvas id="myChart"></canvas>
</div>

  </div>

</template>

<script>

    export default {

        data() {
          return {
            meta: [],
            data: [],
          }
        },

        filters: {
        },

        mounted() {
          axios.get('/api/statistics').then(function(response) {
            this.meta = response.data.meta
            this.data = response.data.data

            var ctx = document.getElementById("myChart").getContext('2d');

            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                  labels: _.map(this.data, 'created_at'),
                  datasets: [{
                    label: 'Emails received by date',
                    data: _.map(this.data, 'emails_received'),
                    borderColor: '#e65722',
                    backgroundColor: '#FDBCB4'
                  }]
                },
            });

          }.bind(this));
        },

        methods: {
        }
    }
</script>
