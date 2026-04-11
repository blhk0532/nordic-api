<div class="h-full ">
    @script
        <script>
            window.ChatWidget = () => {
                return {
                    show: false,
                    showActiveComponent: true,
                    activeWidgetComponent: false,
                    componentHistory: [],
                    listeners: [],
                    //current component attributes
                    closeOnEscape: false,
                    closeOnEscapeIsForceful: false,
                    dispatchCloseEvent: false,
                    destroyOnClose: false,
                    closeModalOnClickAway:false,
                    closeChatWidgetOnEscape(trigger) {

                        ///Only proceed if the trigger is for ChatDrawer
                        if (trigger.modalType !== 'ChatWidget') {
                            return;
                        }

                        //check if canCloseOnEsp
                        if (this.closeOnEscape === false) {
                            return;
                        }

                        //Fire closingModalOnEscape:event to parent
                        if (!this.closingModal('closingModalOnEscape')) {
                            return;
                        }



                        //check if should also close all children modal when this current on is closed
                        const force = this.closeOnEscapeIsForceful === true;
                        this.closeWidget(force);

                    },
                    closingModal(eventName) {
                        const widgetComponents = this.$wire.get('widgetComponents') || {};
                        const activeComponent = widgetComponents[this.activeWidgetComponent];

                        if (!activeComponent || !activeComponent.name) {
                            return true; // Allow closing if component doesn't exist
                        }

                        const componentName = activeComponent.name;

                        var params = {
                            id: this.activeWidgetComponent,
                            closing: true,
                        };

                        Livewire.dispatchTo(componentName, eventName, params);

                        return params.closing;
                    },

                    closeWidget(force = false, skipPreviousModals = 0, destroySkipped = false) {

                        if (this.show === false) {
                            return;
                        }

                        //Check if should completley destroy component on close
                        //Meaning state won't be retained if component is opened again
                        if (this.destroyOnClose === true) {

                            Livewire.dispatch('destroyChatWidget', {
                                id: this.activeWidgetComponent
                            });
                        }

                        const id = this.componentHistory.pop();
                        if (id && !force) {
                            if (id) {
                                this.setActiveWidgetComponent(id, true);
                            } else {
                                this.setShowPropertyTo(false);
                            }
                        } else {
                            this.setShowPropertyTo(false);
                        }

                    },

                    setActiveWidgetComponent(id, skip = false) {
                        this.setShowPropertyTo(true);

                        if (this.activeWidgetComponent !== false && skip === false) {
                            this.componentHistory.push(this.activeWidgetComponent);
                        }

                        let focusableTimeout = 50;

                        if (this.activeWidgetComponent === false) {
                            // First time opening
                            this.activeWidgetComponent = id;
                            this.showActiveComponent = true;
                        } else if (this.activeWidgetComponent !== id) {
                            // Switching between components
                            this.showActiveComponent = false;
                            focusableTimeout = 400;

                            setTimeout(() => {
                                this.activeWidgetComponent = id;
                                this.showActiveComponent = true;
                            }, 300);
                        } else {
                            // Same component, already active - ensure it's visible
                            this.showActiveComponent = true;
                        }

                        // Fetch modal attributes and set Alpine properties
                        const attributes = this.$wire.get('widgetComponents')[id]?.modalAttributes || {};
                        this.closeOnEscape = attributes.closeOnEscape ?? false;
                        this.closeOnEscapeIsForceful = attributes.closeOnEscapeIsForceful ?? false;
                        this.dispatchCloseEvent = attributes.dispatchCloseEvent ?? false;
                        this.destroyOnClose = attributes.destroyOnClose ?? false;
                        this.closeModalOnClickAway = attributes.closeModalOnClickAway ?? false;

                        this.$nextTick(() => {
                            let focusable = this.$refs[id]?.querySelector('[autofocus]');
                            if (focusable) {
                                setTimeout(() => {
                                    focusable.focus();
                                }, focusableTimeout);
                            }
                        });
                    },

                    setShowPropertyTo(show) {
                        this.show = show;
                        if (show) {
                            document.body.classList.add('overflow-y-hidden');
                        } else {
                            document.body.classList.remove('overflow-y-hidden');

                            setTimeout(() => {
                                this.activeWidgetComponent = false;
                                this.$wire.resetState();

                                //Notify listeners that chat is

                            }, 300);

                            const conversation =  this.$wire.selectedConversationId;
                                Livewire.dispatch('chat-closed', {
                                    conversation:conversation
                                });


                        }


                    },
                    init() {

                        /*! Changed the event to closeChatWidget in order to not interfere with the main modal */
                        this.listeners.push(Livewire.on('closeChatWidget', (data) => { this.closeWidget(data?.force ?? false, data?.skipPreviousModals ?? 0, data ?.destroySkipped ?? false); }));

                        /*! Changed listener name to activeChatWidgetComponentChanged to not interfer with main modal*/
                        this.listeners.push(Livewire.on('activeChatWidgetComponentChanged', ({id}) => {
                            // Add a small delay to ensure the widget component is ready
                            this.$nextTick(() => {
                                this.setActiveWidgetComponent(id);
                            });
                        }));
                    },
                    destroy() {
                        this.listeners.forEach((listener) => {
                            listener();
                        });
                    }
                };
            }
        </script>
    @endscript


    <div
    x-data="{
        selectedConversationId:null,
        get chatIsOpen(){

            return $wire.selectedConversationId !==null;

        }
    }"

     class ='w-full h-full bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)] border border-[var(--wc-light-secondary)] dark:border-[var(--wc-dark-secondary)] flex overflow-hidden rounded-lg'>
      <div :class="chatIsOpen && 'hidden'" class="relative  w-full h-full sm:border-r border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)]    md:w-[500px] lg:w-[500px] xl:w-[500px] shrink-0 overflow-y-auto  ">


      <livewire:wirechat.chats :widget="true" :panel="$this->panelId()" />
      </div>
      <main
           x-data="ChatWidget()"
           x-on:open-chat.window="
               const conversationId = $event.detail.conversation;
               // Toggle: if same conversation is already open, close it
               if ($wire.selectedConversationId === conversationId && $wire.selectedConversationId !== null) {
                   // Close the chat
                   setShowPropertyTo(false);
                   $wire.selectedConversationId = null;
                   $wire.resetState();
               } else {
                   // Open new conversation - set the ID which will trigger updatedSelectedConversationId
                   $wire.selectedConversationId = conversationId;
               }
           "
           x-on:close-chat.stop.window="setShowPropertyTo(false)"
           x-on:keydown.escape.stop.window="closeChatWidgetOnEscape({ modalType: 'ChatWidget', event: $event });"
           aria-modal="true"
           tabindex="0"
           class="w-full h-full min-h-full grid relative grow  focus:outline-hidden focus:border-none"
           :class="!chatIsOpen && 'hidden md:grid'"
           style="contain:content;">
            <div
                x-cloak
                x-show="show && showActiveComponent && activeWidgetComponent !== false"
                x-transition:enter="ease-out duration-100"
                x-transition:enter-start="opacity-0 -translate-x-full"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-full"
                class="fixed inset-0"
                id="chatwidget-container"
                aria-modal="true">
                @forelse($widgetComponents as $id => $component)
                    <div x-show.immediate="activeWidgetComponent == @js($id)"
                         x-ref="@js($id)"
                         wire:key="key-{{$id }}"
                         class="h-full">

                    @livewire($component['name'], ['conversation'=> $component['conversation'] ,'widget'=>true,'panel'=>$this->panel], key($id))
                    </div>
                @empty
                @endforelse
            </div>

            <div  x-show="!show && !chatIsOpen " class="m-auto  justify-center flex gap-3 flex-col  items-center ">

                <h4 class="font-medium p-2 px-3 rounded-full font-semibold bg-[var(--wc-light-secondary)] dark:bg-[var(--wc-dark-secondary)] dark:text-white dark:font-normal">@lang('wirechat::widgets.wirechat.messages.welcome')</h4>

            </div>



      </main>
  </div>





</div>
