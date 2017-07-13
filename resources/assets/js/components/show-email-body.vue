<template>
  <div>

  <div class="level-left">

    <div class="tabs is-small is-toggle">

      <ul>
        <li v-for="bodyType in bodyTypes" :class="classesLink(bodyType)" :disabled="isDisabled(bodyType)"  @click="getContent(bodyType.accept)">
          <a>
            <span class="icon is-small">
              <i :class="classes(bodyType)"></i>
            </span>
            <span v-text="bodyType.label"></span>
          </a>
        </li>
      </ul>
    </div>

  </div>

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
            return [bodyType.isActive ? 'is-active' : '']
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
    min-height: 100%;
    border: 1px solid #363636;
    margin: 20px auto;
    height: 400px;
    width: 100%;

  }
</style>

