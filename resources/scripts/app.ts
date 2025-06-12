import Alpine from 'alpinejs';

window.addEventListener( 'DOMContentLoaded', () => {

    Alpine.magic('parent', (el, { Alpine }) => {
        return Alpine.mergeProxies(
            Alpine.closestDataStack(el).slice(1)
        )
    })

    Alpine.data( 'initSection', ( product_id: number, section_type: string ) => ( {
        init()
        {
            fetch( CCF_PARAMS.api_url + '/getFields/' + section_type + '/' + product_id,
              {
                  // eslint-disable-line
                  method: 'GET',
                  mode: 'cors',
                  credentials: 'same-origin',
                  redirect: 'follow',
                  referrerPolicy: 'no-referrer',
                  headers: {
                      'X-WP-Nonce': CCF_PARAMS.restNonce, // eslint-disable-line
                  }
              }
            ).then(response => response.json())
            .then( ( response ) => {
              this.section_fields = response.fields;
            } );

        },
        field_name: '',
        section_fields: []
    } ) );

    Alpine.data('imageField', (section_fields: any, field_name: string, default_value: string) => ({
        field_name: field_name,
        default_value: default_value,
        field_value: '',
        image_url: '',
        openMediaLibrary() {
            const frame = wp.media({
                title: 'Select or Upload Image',
                button: { text: 'Use this image' },
                multiple: false
            });

            frame.on('select', () => {
                const attachment = frame.state().get('selection').first().toJSON();
                this.field_value = attachment.id;
                this.image_url = attachment.url;
            });

            frame.open();
        },
        init() {
            this.field_value = section_fields[this.field_name] !== undefined ? section_fields[this.field_name] : this.default_value;
            if (this.field_value) {
                wp.media.attachment(this.field_value).fetch().then(() => {
                    this.image_url = wp.media.attachment(this.field_value).get('url');
                });
            }
        }
    }));
    

    window.Alpine = Alpine;
    Alpine.start();
} );
