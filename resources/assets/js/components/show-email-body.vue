<template>

  <div>

    <p class="field">
      <a v-for="bodyType in bodyTypes" :class="classesLink(bodyType)" :disabled="isDisabled(bodyType)"  @click="getContent(bodyType.accept)">
        <span class="icon">
          <i :class="classes(bodyType)"></i>
        </span>
        <span v-text="bodyType.label"></span>
      </a>
    </p>

    <div>
      <i-frame class="my-frame">
        <div v-html="body"></div>
      </i-frame> 
    </div>

  </div>

</template>

<script>

    export default {

        props: ['id', 'email'],

        data() {
          return {
            bodyTypes: [
                {label: 'Html', icon: 'fa-image', accept: 'text/html', br: false}, 
                {label: 'Text', icon: 'fa-file-text-o', accept: 'text/plain', br: true}, 
                {label: 'Raw', icon: 'fa-sticky-note', accept: 'message/rfc822', br: true}
                ],
            body: null,
          }
        },

        mounted() {
            this.getContent();
        },

        methods: {

          classesLink(bodyType)
          {
            return ['button', bodyType.isActive ? 'is-success' : '']
          },

          classes(bodyType) {
            return ['fa', bodyType.icon]
          },

          isDisabled(bodyType) {

            if (bodyType.label == 'Html')
            {
              return !this.email.is_html;
            }
            else if (bodyType.label == 'Text')
            {
              return !this.email.is_text;
            }
            else
            {
              return false;
            }
          },

          getContent(accept = 'text/html,text/plain') {

              axios({
                method:'get',
                url:'/api/v1/emails/' + this.id,
                headers: {'Accept': accept}
              })
              .then(function (response) {

                this.bodyTypes.forEach(bodyType => {
                  if (response.headers['content-type'].indexOf(bodyType.accept))
                  {
                    if (bodyType.br)
                      this.body = response.data.replace(/(\r\n|\n|\r)/gm, "<br>");
                    else
                      this.body = response.data
                    bodyType.isActive = false
                  }
                  else
                  {
                    bodyType.isActive = true
                  }
                })

              }.bind(this));  

          },
        }
    }
</script>


<style scoped>
  .my-frame {
    border: 1px solid #CCC;
    box-shadow: 0 0 3px 2x rgba(0,0,0,.3);
    margin: 20px auto;
    height: 200px;
    width: 95%;
  }
</style>