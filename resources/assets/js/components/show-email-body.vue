<template>
  <div>

    <nav class="level">

      <div class="level-left">
        <div class="tabs is-toggle">
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

      <div class="level-right is-small">
        <nav class="panel">
          <p class="panel-heading">
            {{ this.email.attachments.length }} attachments
          </p>
          <a v-for="attachment in this.email.attachments" class="panel-block" v-bind:href="'/emails/' + email.id + '/attachments/' + attachment.id">
            <span class="panel-icon">
              <i :class="classesContentType(attachment.content_type)"></i>
            </span>
            {{ attachment.file_name }} ({{ attachment.size_for_human }})
          </a>
        </nav>
      </div>

    </nav>

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
                {label: 'Raw', icon: 'fa-sticky-note', accept: 'message/rfc2822', br: true}
                ],
            body: null,
          }
        },

        mounted() {
            this.getContent();
        },

        methods: {

          classesContentType(contentType)
          {
            if (contentType.startsWith("image"))
            {
              return ['fa', 'fa-image-o']
            }
            else if (contentType.startsWith("audio"))
            {
              return ['fa', 'fa-audio-o']
            }
            else if (contentType.startsWith("text"))
            {
              return ['fa', 'fa-text-o']
            }
            else if (contentType.startsWith("video"))
            {
              return ['fa', 'fa-video-o']
            }
            else if (contentType.includes("excel") || contentType.includes("spreadsheet"))
            {
              return ['fa', 'fa-excel-o']
            }
            else if (contentType.includes("powerpoint") || contentType.includes("presentation"))
            {
              return ['fa', 'fa-powerpoint-o']
            }
            else if (contentType.includes("zip"))
            {
              return ['fa', 'fa-archive-o']
            }
            else if (contentType.includes("pdf"))
            {
              return ['fa', 'fa-pdf-o']
            }
            else if (contentType.includes("word"))
            {
              return ['fa', 'fa-word-o']
            }
            else
            {
              return ['fa', 'fa-file-o']
            }
          },

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
              return !this.email.has_html;
            }
            else if (bodyType.label == 'Text')
            {
              return !this.email.has_text;
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
                  if (response.headers['content-type'].indexOf(bodyType.accept) > -1)
                  {
                    if (bodyType.br)
                    {
                      this.body = response.data.replace(/(\r\n|\n|\r)/gm, "<br>");
                    }
                    else
                    {
                      this.body = response.data
                    }
                    bodyType.isActive = true
                  }
                  else
                  {
                    bodyType.isActive = false
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
    border: 1px solid #d6dbe1;
    margin: 20px auto;
    height: 400px;
    width: 100%;

  }
</style>

