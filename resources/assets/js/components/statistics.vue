<template>

  <div>
    <nav class="level"> 
      <div class="level-item has-text-centered"> 
        <div> 
          <p class="heading">Emails received</p> 
          <p class="title" v-text="metadata.emails_received"></p> 
        </div> 
      </div> 
      <div class="level-item has-text-centered"> 
        <div> 
          <p class="heading">Inboxes created</p> 
          <p class="title" v-text="metadata.inboxes_created"></p> 
        </div> 
      </div> 
      <div class="level-item has-text-centered"> 
        <div> 
          <p class="heading">Storage used</p> 
          <p class="title" v-text="metadata.storage_used_for_human"></p> 
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
            metadata: [],
            data: [],
          }
        },

        filters: {
        },

        mounted() {
          axios.get('/api/v1/statistics').then(function(response) {
            this.metadata = response.data.metadata
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






// var myChart = new Chart(ctx, {
//     type: 'bar',
//     data: {
//         labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
//         datasets: [{
//             label: '# of Votes',
//             data: [12, 19, 3, 5, 2, 3],
//             backgroundColor: [
//                 'rgba(255, 99, 132, 0.2)',
//                 'rgba(54, 162, 235, 0.2)',
//                 'rgba(255, 206, 86, 0.2)',
//                 'rgba(75, 192, 192, 0.2)',
//                 'rgba(153, 102, 255, 0.2)',
//                 'rgba(255, 159, 64, 0.2)'
//             ],
//             borderColor: [
//                 'rgba(255,99,132,1)',
//                 'rgba(54, 162, 235, 1)',
//                 'rgba(255, 206, 86, 1)',
//                 'rgba(75, 192, 192, 1)',
//                 'rgba(153, 102, 255, 1)',
//                 'rgba(255, 159, 64, 1)'
//             ],
//             borderWidth: 1
//         }]
//     },
//     options: {
//         scales: {
//             yAxes: [{
//                 ticks: {
//                     beginAtZero:true
//                 }
//             }]
//         }
//     }
// });





        },

        methods: {
        }
    }
</script>
