export default function calendarEvent({
                                          event,
                                          timeText,
                                          view,
                                          hasContextMenu,
                                      }
) {
    return {
        event,
        contextMenu: null,

        init: function () {
            if (!this.$el || this.$el.classList.contains('ec-preview')) {
                return
            }

            if (hasContextMenu) {
                this.initializeContextMenu()
            }
            this.$el.setAttribute('data-event-id', event.id)
            // marker for debugging and easy selection in the browser
            this.$el.setAttribute('data-calendar-event', 'true')

            this.$el.addEventListener('mouseenter', () => {
                document.querySelectorAll(`.ec-event[data-event-id="${event.id}"]`).forEach(el => {
                    el.classList.add('gu-hover')
                })
            })

            this.$el.addEventListener('mouseleave', () => {
                document.querySelectorAll(`.ec-event[data-event-id="${event.id}"]`).forEach(el => {
                    el.classList.remove('gu-hover')
                })
            })
            // Preloading
            // this.$el.addEventListener('mouseenter', () => {
            //     this.contextMenu.loadActions(this.event)
            // })
        },

        initializeContextMenu: function () {
            const element = document.querySelector('[calendar-context-menu]')
            if (element) {
                this.contextMenu = Alpine.$data(element)
            }
        },

        /**
         * Called when an event is clicked, if event clicking is enabled in the calendar.
         * @param info
         */
        onClick: function (info) {
            if (info.event.extendedProps.url) {
                window.open(
                    this.event.extendedProps.url,
                    this.event.extendedProps.url_target ?? '_blank'
                )
                return
            }

            const data = {
                event: info.event,
                view: info.view,
                tzOffset: -new Date().getTimezoneOffset()
            }

            if (hasContextMenu && this.contextMenu) {
                this.contextMenu.loadActions('eventClick', data)
                this.contextMenu.openMenu(
                    info.jsEvent,
                    this.$el
                )
                return
            }

            // Debug: log click and data so we can confirm client-side invocation
            try {
                console.log('[calendar-event] onClick', {data})
            } catch (e) {
                // ignore
            }

            this.$wire.onEventClickJs(data)
        },
    }
}
