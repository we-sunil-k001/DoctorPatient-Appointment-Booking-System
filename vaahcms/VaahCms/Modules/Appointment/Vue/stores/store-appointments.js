import {ref, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import axios from "axios";

let model_namespace = 'VaahCms\\Modules\\Appointment\\Models\\Appointment';


let base_url = document.getElementsByTagName('base')[0].getAttribute("href");
let ajax_url = base_url + "/appointment/appointments";

let empty_states = {
    query: {
        page: null,
        rows: null,
        filter: {
            q: null,
            is_active: null,
            trashed: null,
            sort: null,
        },
    },
    action: {
        type: null,
        items: [],
    }
};

export const useAppointmentStore = defineStore({
    id: 'appointments',
    state: () => ({
        base_url: base_url,
        ajax_url: ajax_url,
        model: model_namespace,
        assets_is_fetching: true,
        app: null,
        assets: null,
        rows_per_page: [10,20,30,50,100,500],
        list: null,
        item: null,
        fillable:null,
        empty_query:empty_states.query,
        empty_action:empty_states.action,
        query: vaah().clone(empty_states.query),
        action: vaah().clone(empty_states.action),
        search: {
            delay_time: 600, // time delay in milliseconds
            delay_timer: 0 // time delay in milliseconds
        },
        route: null,
        watch_stopper: null,
        route_prefix: 'appointments.',
        view: 'large',
        show_filters: false,
        list_view_width: 12,
        form: {
            type: 'Create',
            action: null,
            is_button_loading: null
        },
        is_list_loading: null,
        count_filters: 0,
        list_selected_menu: [],
        list_bulk_menu: [],
        list_create_menu: [],
        item_menu_list: [],
        item_menu_state: null,
        form_menu_list: [],
        // doctor_details: null

        //Tab View --------------
        visible: false,
        activeTabIndex : 0,
        tabs: [{ header: 'Upload File', disabled: false },
            { header: 'Mapping', disabled: true },
            { header: 'Publish Data', disabled: true }],
        //Import File --------
        file_to_upload: null,
        file_date: null,
        // Variables to store selected dropdown options and CSV data
        csv_headers : [],
        csv_data : [],
        selected_patient_name : null,
        selected_patient_email : null,
        selected_doctor_name : null,
        selected_doctor_email : null,
        selected_medical_concern : null,
        selected_appointment_date : null,
        selected_appointment_time : null,
        form_data : [],
        table_data : [],
        response_errors : []

    }),
    getters: {

    },
    actions: {
        //---------------------------------------------------------------------
        async onLoad(route)
        {
            /**
             * Set initial routes
             */
            this.route = route;

            /**
             * Update with view and list css column number
             */
            await this.setViewAndWidth(route.name);

            await(this.query = vaah().clone(this.empty_query));

            await this.countFilters(this.query);

            /**
             * Update query state with the query parameters of url
             */
            await this.updateQueryFromUrl(route);
        },
        //---------------------------------------------------------------------
        setRowClass(data){
            return [{ 'bg-gray-200': data.id == this.route.params.id }];
        },
        //---------------------------------------------------------------------
        setViewAndWidth(route_name)
        {
            switch(route_name)
            {
                case 'appointments.index':
                    this.view = 'large';
                    this.list_view_width = 12;
                    break;
                default:
                    this.view = 'small';
                    this.list_view_width = 6;
                    this.show_filters = false;
                    break
            }
        },
        //---------------------------------------------------------------------
        async updateQueryFromUrl(route)
        {
            if(route.query)
            {
                if(Object.keys(route.query).length > 0)
                {
                    for(let key in route.query)
                    {
                        this.query[key] = route.query[key]
                    }
                    if(this.query.rows){
                        this.query.rows = parseInt(this.query.rows);
                    }
                    this.countFilters(route.query);
                }
            }
        },
        //---------------------------------------------------------------------
        watchRoutes(route)
        {
            //watch routes
            this.watch_stopper = watch(route, (newVal,oldVal) =>
                {

                    if(this.watch_stopper && !newVal.name.includes(this.route_prefix)){
                        this.watch_stopper();

                        return false;
                    }

                    this.route = newVal;

                    if(newVal.params.id){
                        this.getItem(newVal.params.id);
                    }

                    this.setViewAndWidth(newVal.name);

                }, { deep: true }
            )
        },
        //---------------------------------------------------------------------
        watchStates()
        {
            watch(this.query.filter, (newVal,oldVal) =>
                {
                    this.delayedSearch();
                },{deep: true}
            )
        },
        //---------------------------------------------------------------------
         watchItem(name)
          {
              if(name && name !== "")
              {
                  this.item.name = vaah().capitalising(name);
                  this.item.slug = vaah().strToSlug(name);
              }else{
                  this.item.slug = name;
              }
          },
        //---------------------------------------------------------------------
        async getAssets() {

            if(this.assets_is_fetching === true){
                this.assets_is_fetching = false;

                await vaah().ajax(
                    this.ajax_url+'/assets',
                    this.afterGetAssets,
                );
            }
        },
        //---------------------------------------------------------------------
        afterGetAssets(data, res)
        {
            if(data)
            {
                this.assets = data;
                if(!this.query.rows && data.rows)
                {
                    this.query.rows = data.rows;
                    this.empty_query.rows = data.rows;
                }

                if(this.route.params && !this.route.params.id){
                    this.item = vaah().clone(data.empty_item);
                }

            }
        },
        //---------------------------------------------------------------------
        async getList() {
            let options = {
                query: vaah().clone(this.query)
            };
            await vaah().ajax(
                this.ajax_url,
                this.afterGetList,
                options
            );
        },
        //---------------------------------------------------------------------
        afterGetList: function (data, res)
        {
            if(data)
            {
                this.list = data;
            }
        },
        //---------------------------------------------------------------------

        async getItem(id) {
            if(id){
                await vaah().ajax(
                    ajax_url+'/'+id,
                    this.getItemAfter
                );
            }
        },
        //---------------------------------------------------------------------
        async getItemAfter(data, res)
        {
            if(data)
            {
                this.item = data;
                this.end_time_temp = data.working_hours_end;
            }else{
                this.$router.push({name: 'appointments.index',query:this.query});
            }
            await this.getItemMenu();
            await this.getFormMenu();
        },
        //---------------------------------------------------------------------
        isListActionValid()
        {

            if(!this.action.type)
            {
                vaah().toastErrors(['Select an action type']);
                return false;
            }

            if(this.action.items.length < 1)
            {
                vaah().toastErrors(['Select records']);
                return false;
            }

            return true;
        },
        //---------------------------------------------------------------------
        async updateList(type = null){

            if(!type && this.action.type)
            {
                type = this.action.type;
            } else{
                this.action.type = type;
            }

            if(!this.isListActionValid())
            {
                return false;
            }


            let method = 'PUT';

            switch (type)
            {
                case 'delete':
                    method = 'DELETE';
                    break;
            }

            let options = {
                params: this.action,
                method: method,
                show_success: false
            };
            await vaah().ajax(
                this.ajax_url,
                this.updateListAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        async updateListAfter(data, res) {
            if(data)
            {
                this.action = vaah().clone(this.empty_action);
                await this.getList();
            }
        },
        //---------------------------------------------------------------------
        async listAction(type = null){

            if(!type && this.action.type)
            {
                type = this.action.type;
            } else{
                this.action.type = type;
            }

            let url = this.ajax_url+'/action/'+type
            let method = 'PUT';

            switch (type)
            {
                case 'delete':
                    url = this.ajax_url
                    method = 'DELETE';
                    break;
                case 'delete-all':
                    method = 'DELETE';
                    break;
            }

            this.action.filter = this.query.filter;

            let options = {
                params: this.action,
                method: method,
                show_success: false
            };
            await vaah().ajax(
                url,
                this.updateListAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        async itemAction(type, item=null){
            if(!item)
            {
                item = this.item;
            }

            this.form.action = type;

            let ajax_url = this.ajax_url;

            let options = {
                method: 'post',
            };

            /**
             * Learn more about http request methods at
             * https://www.youtube.com/watch?v=tkfVQK6UxDI
             */
            switch (type)
            {
                /**
                 * Create a record, hence method is `POST`
                 * https://docs.vaah.dev/guide/laravel.html#create-one-or-many-records
                 */
                case 'create-and-new':
                case 'create-and-close':
                case 'create-and-clone':
                    options.method = 'POST';
                    options.params = item;
                    break;

                /**
                 * Update a record with many columns, hence method is `PUT`
                 * https://docs.vaah.dev/guide/laravel.html#update-a-record-update-soft-delete-status-change-etc
                 */
                case 'save':
                case 'save-and-close':
                case 'save-and-clone':
                    options.method = 'PUT';
                    options.params = item;
                    ajax_url += '/'+item.id
                    break;
                /**
                 * Delete a record, hence method is `DELETE`
                 * and no need to send entire `item` object
                 * https://docs.vaah.dev/guide/laravel.html#delete-a-record-hard-deleted
                 */
                case 'delete':
                    options.method = 'DELETE';
                    ajax_url += '/'+item.id
                    break;
                /**
                 * Update a record's one column or very few columns,
                 * hence the method is `PATCH`
                 * https://docs.vaah.dev/guide/laravel.html#update-a-record-update-soft-delete-status-change-etc
                 */
                default:
                    options.method = 'PATCH';
                    ajax_url += '/'+item.id+'/action/'+type;
                    break;
            }

            await vaah().ajax(
                ajax_url,
                this.itemActionAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        async itemActionAfter(data, res)
        {
            if(data)
            {
                await this.getList();
                await this.formActionAfter(data);
                this.getItemMenu();
                this.getFormMenu();
            }
        },
        //---------------------------------------------------------------------
        async formActionAfter (data)
        {
            switch (this.form.action)
            {
                case 'create-and-new':
                case 'save-and-new':
                    this.setActiveItemAsEmpty();
                    break;
                case 'create-and-close':
                case 'save-and-close':
                    this.setActiveItemAsEmpty();
                    this.$router.push({name: 'appointments.index',query:this.query});
                    break;
                case 'save-and-clone':
                case 'create-and-clone':
                    this.item.id = null;
                    this.$router.push({name: 'appointments.form',query:this.query,params: { id: null }});
                    await this.getFormMenu();
                    break;
                case 'trash':
                case 'restore':
                case 'save':
                    if(this.item && this.item.id){
                        this.item = data;
                    }
                    break;
                case 'delete':
                    this.item = null;
                    this.toList();
                    break;
            }
        },
        //---------------------------------------------------------------------
        async toggleIsActive(item)
        {
            if(item.is_active)
            {
                await this.itemAction('activate', item);
            } else{
                await this.itemAction('deactivate', item);
            }
        },
        //---------------------------------------------------------------------
        async paginate(event) {
            this.query.page = event.page+1;
            await this.getList();
            await this.updateUrlQueryString(this.query);
        },
        //---------------------------------------------------------------------
        async reload()
        {
            await this.getAssets();
            await this.getList();
        },
        //---------------------------------------------------------------------
        async getFormInputs () {
            let params = {
                model_namespace: this.model,
                except: this.assets.fillable.except,
            };

            let url = this.ajax_url+'/fill';

            await vaah().ajax(
                url,
                this.getFormInputsAfter,
            );
        },
        //---------------------------------------------------------------------
        getFormInputsAfter: function (data, res) {
            if(data)
            {
                let self = this;
                Object.keys(data.fill).forEach(function(key) {
                    self.item[key] = data.fill[key];
                });
            }
        },

        //---------------------------------------------------------------------

        //---------------------------------------------------------------------
        onItemSelection(items)
        {
            this.action.items = items;
        },
        //---------------------------------------------------------------------
        setActiveItemAsEmpty()
        {
            this.item = vaah().clone(this.assets.empty_item);
        },
        //---------------------------------------------------------------------
        confirmDelete()
        {
            if(this.action.items.length < 1)
            {
                vaah().toastErrors(['Select a record']);
                return false;
            }
            this.action.type = 'delete';
            vaah().confirmDialogDelete(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmDeleteAll()
        {
            this.action.type = 'delete-all';
            vaah().confirmDialogDelete(this.listAction);
        },
        //---------------------------------------------------------------------
        confirmAction(action_type,action_header)
        {
            this.action.type = action_type;
            vaah().confirmDialog(action_header,'Are you sure you want to do this action?',
                this.listAction,null,'p-button-primary');
        },
        //---------------------------------------------------------------------
        async delayedSearch()
        {
            let self = this;
            this.query.page = 1;
            this.action.items = [];
            clearTimeout(this.search.delay_timer);
            this.search.delay_timer = setTimeout(async function() {
                await self.updateUrlQueryString(self.query);
                await self.getList();
            }, this.search.delay_time);
        },
        //---------------------------------------------------------------------
        async updateUrlQueryString(query)
        {
            //remove reactivity from source object
            query = vaah().clone(query)

            //create query string
            let query_string = qs.stringify(query, {
                skipNulls: true,
            });
            let query_object = qs.parse(query_string);

            if(query_object.filter){
                query_object.filter = vaah().cleanObject(query_object.filter);
            }

            //reset url query string
            await this.$router.replace({query: null});

            //replace url query string
            await this.$router.replace({query: query_object});

            //update applied filters
            this.countFilters(query_object);

        },
        //---------------------------------------------------------------------
        countFilters: function (query)
        {
            this.count_filters = 0;
            if(query && query.filter)
            {
                let filter = vaah().cleanObject(query.filter);
                this.count_filters = Object.keys(filter).length;
            }
        },
        //---------------------------------------------------------------------
        async clearSearch()
        {
            this.query.filter.q = null;
            await this.updateUrlQueryString(this.query);
            await this.getList();
        },
        //---------------------------------------------------------------------
        async resetQuery()
        {
            //reset query strings
            await this.resetQueryString();

            //reload page list
            await this.getList();
        },
        //---------------------------------------------------------------------
        async resetQueryString()
        {
            for(let key in this.query.filter)
            {
                this.query.filter[key] = null;
            }
            await this.updateUrlQueryString(this.query);
        },
        //---------------------------------------------------------------------
        closeForm()
        {
            this.$router.push({name: 'appointments.index',query:this.query})
        },
        //---------------------------------------------------------------------
        toList()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.$router.push({name: 'appointments.index',query:this.query})
        },

        //---------------------------------------------------------------------
        toForm()
        {
            this.item = vaah().clone(this.assets.empty_item);
            this.getFormMenu();
            this.$router.push({name: 'appointments.form',query:this.query})
        },
        //---------------------------------------------------------------------
        toView(item)
        {
            if(!this.item || !this.item.id || this.item.id !== item.id){
                this.item = vaah().clone(item);
            }
            this.$router.push({name: 'appointments.view', params:{id:item.id},query:this.query})
        },
        //---------------------------------------------------------------------
        toEdit(item)
        {
            if(!this.item || !this.item.id || this.item.id !== item.id){
                this.item = vaah().clone(item);
            }
            this.$router.push({name: 'appointments.form', params:{id:item.id},query:this.query})
        },
        //---------------------------------------------------------------------
        isViewLarge()
        {
            return this.view === 'large';
        },
        //---------------------------------------------------------------------
        getActionWidth()
        {
            let width = 100;
            if(!this.isViewLarge())
            {
                width = 80;
            }
            return width+'px';
        },
        //---------------------------------------------------------------------
        getActionLabel()
        {
            let text = null;
            if(this.isViewLarge())
            {
                text = 'Actions';
            }

            return text;
        },

        //---------------------------------------------------------------------
        hasPermission(permissions, slug) {
            return vaah().hasPermission(permissions, slug);
        },
        //---------------------------------------------------------------------

        async getListSelectedMenu()
        {
            this.list_selected_menu = [
                {
                    label: 'Activate',
                    command: async () => {
                        await this.updateList('activate')
                    }
                },
                {
                    label: 'Deactivate',
                    command: async () => {
                        await this.updateList('deactivate')
                    }
                },
                {
                    separator: true
                },
                {
                    label: 'Trash',
                    icon: 'pi pi-times',
                    command: async () => {
                        await this.updateList('trash')
                    }
                },
                {
                    label: 'Restore',
                    icon: 'pi pi-replay',
                    command: async () => {
                        await this.updateList('restore')
                    }
                },
                {
                    label: 'Delete',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.confirmDelete()
                    }
                },
            ]

        },
        //---------------------------------------------------------------------
        getListBulkMenu()
        {
            this.list_bulk_menu = [
                {
                    label: 'Mark all as active',
                    command: async () => {
                        await this.confirmAction('activate-all','Mark all as active');
                    }
                },
                {
                    label: 'Mark all as inactive',
                    command: async () => {
                        await this.confirmAction('deactivate-all','Mark all as inactive');
                    }
                },
                {
                    separator: true
                },
                {
                    label: 'Trash All',
                    icon: 'pi pi-times',
                    command: async () => {
                        await this.confirmAction('trash-all','Trash All');
                    }
                },
                {
                    label: 'Restore All',
                    icon: 'pi pi-replay',
                    command: async () => {
                        await this.confirmAction('restore-all','Restore All');
                    }
                },
                {
                    label: 'Delete All',
                    icon: 'pi pi-trash',
                    command: async () => {
                        this.confirmDeleteAll();
                    }
                },
            ];
        },
        //---------------------------------------------------------------------
        getItemMenu()
        {
            let item_menu = [];

            if(this.item && this.item.deleted_at)
            {

                item_menu.push({
                    label: 'Restore',
                    icon: 'pi pi-refresh',
                    command: () => {
                        this.itemAction('restore');
                    }
                });
            }

            if(this.item && this.item.id && !this.item.deleted_at)
            {
                item_menu.push({
                    label: 'Trash',
                    icon: 'pi pi-times',
                    command: () => {
                        this.itemAction('trash');
                    }
                });
            }

            item_menu.push({
                label: 'Delete',
                icon: 'pi pi-trash',
                command: () => {
                    this.confirmDeleteItem('delete');
                }
            });

            this.item_menu_list = item_menu;
        },
        //---------------------------------------------------------------------
        async getListCreateMenu()
        {
            let form_menu = [];

            form_menu.push(
                {
                    label: 'Create 100 Records',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.listAction('create-100-records');
                    }
                },
                {
                    label: 'Create 1000 Records',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.listAction('create-1000-records');
                    }
                },
                {
                    label: 'Create 5000 Records',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.listAction('create-5000-records');
                    }
                },
                {
                    label: 'Create 10,000 Records',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.listAction('create-10000-records');
                    }
                },

            )

            this.list_create_menu = form_menu;

        },

        //---------------------------------------------------------------------
        confirmDeleteItem()
        {
            this.form.type = 'delete';
            vaah().confirmDialogDelete(this.confirmDeleteItemAfter);
        },
        //---------------------------------------------------------------------
        confirmDeleteItemAfter()
        {
            this.itemAction('delete', this.item);
        },
        //---------------------------------------------------------------------
        async getFormMenu()
        {
            let form_menu = [];

            if(this.item && this.item.id)
            {
                let is_deleted = !!this.item.deleted_at;
                form_menu = [
                    {
                        label: 'Save & Close',
                        icon: 'pi pi-check',
                        command: () => {

                            this.itemAction('save-and-close');
                        }
                    },
                    {
                        label: 'Save & Clone',
                        icon: 'pi pi-copy',
                        command: () => {

                            this.itemAction('save-and-clone');

                        }
                    },
                    {
                        label: is_deleted ? 'Restore': 'Trash',
                        icon: is_deleted ? 'pi pi-refresh': 'pi pi-times',
                        command: () => {
                            this.itemAction(is_deleted ? 'restore': 'trash');
                        }
                    },
                    {
                        label: 'Delete',
                        icon: 'pi pi-trash',
                        command: () => {
                            this.confirmDeleteItem('delete');
                        }
                    },
                ];

            } else{
                form_menu = [
                    {
                        label: 'Create & Close',
                        icon: 'pi pi-check',
                        command: () => {
                            this.itemAction('create-and-close');
                        }
                    },
                    {
                        label: 'Create & Clone',
                        icon: 'pi pi-copy',
                        command: () => {

                            this.itemAction('create-and-clone');

                        }
                    },
                    {
                        label: 'Reset',
                        icon: 'pi pi-refresh',
                        command: () => {
                            this.setActiveItemAsEmpty();
                        }
                    }
                ];
            }

            form_menu.push({
                label: 'Fill',
                icon: 'pi pi-pencil',
                command: () => {
                    this.getFormInputs();
                }
            },)

            this.form_menu_list = form_menu;

        },


        //-------------------------------------------------------------
        //Custom functions below

        // async fetchDoctorDetails(event)
        // {
        //     alert("hello");
        //     return "hello";
        //     const selectedDoctorId = event.value; // Get the selected doctor ID
        //     if (selectedDoctorId) {
        //         try {
        //             // Fetch doctor details from your API or data source
        //             const response = await axios.get(`backend/appointment/doctors/${selectedDoctorId}`);
        //             doctor_details.value = response.data; // Store the fetched data
        //         } catch (error) {
        //             console.error('Error fetching doctor details:', error);
        //             doctor_details.value = null; // Reset if there's an error
        //         }
        //     } else {
        //         doctor_details.value = null; // Reset if no doctor is selected
        //     }
        // },

        async getDashboardStats() {
            const response = await vaah().ajax(
                ajax_url + '/stats'
            );
            this.getItemAfter(response);
        },


        //Convert to 12-Hour Format
        formatTime(dateString) {
            const date = new Date(dateString);
            let hours = date.getHours();
            const minutes = date.getMinutes();

            const period = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12 || 12; // Convert to 12-hour format
            const formattedMinutes = minutes < 10 ? '0' + minutes : minutes; // Add leading zero if needed

            return `${hours}:${formattedMinutes} ${period}`;
        },

        //Function to Convert time like 10:00 am to UTC
        convertToUTC(timeString) {
            const date = new Date();
            const [time, period] = timeString.split(' ');
            let [hours, minutes] = time.split(':').map(Number);

            // Convert to 24-hour format
            if (period.toLowerCase() === 'pm' && hours !== 12) hours += 12;
            if (period.toLowerCase() === 'am' && hours === 12) hours = 0;

            // Set the current date with the converted time
            date.setHours(hours, minutes, 0, 0);

            return date.toISOString(); // Return UTC in ISO format
        },

        // Add minutes in the existing time
        addMinutesToTime(time, minutesToAdd) {

            //Split the time into hours, minutes, and period (AM/PM)
            const [timePart, period] = time.split(' ');
            let [hours, minutes] = timePart.split(':').map(Number);


            // Convert to 24-hour format for easier calculations
            if (period === 'pm' && hours !== 12) hours += 12;
            if (period === 'am' && hours === 12) hours = 0;

            // Create a new Date object and set the time
            const date = new Date();
            date.setHours(hours);
            date.setMinutes(minutes);


            // Add the specified minutes
            date.setMinutes(date.getMinutes() + minutesToAdd);

            // Get the updated hours and minutes
            let updatedHours = date.getHours();
            const updatedMinutes = date.getMinutes();


            // Convert back to 12-hour format
            const updatedPeriod = updatedHours >= 12 ? 'pm' : 'am';
            updatedHours = updatedHours % 12 || 12; // Convert 0 hour to 12

            // Format minutes to always have two digits
            const formattedMinutes = updatedMinutes < 10 ? '0' + updatedMinutes : updatedMinutes;

            // Return the updated time
            return `${updatedHours}:${formattedMinutes} ${updatedPeriod}`;

        },

        // Import CSV -----------------------------------------------------------

        // Function to convert CSV to JSON
        async csvToJson(csv) {
            const lines = csv.split('\n');
            const result = [];

            // Get the headers
            const headers = lines[0].split(',').map((header) => header.trim());

            // Iterate through the rows
            for (let i = 1; i < lines.length; i++) {
                const obj = {};
                const currentline = lines[i].split(',').map((value) => value.trim());

                for (let j = 0; j < headers.length; j++) {
                    obj[headers[j]] = currentline[j]; // Create key-value pairs
                }

                result.push(obj);
            }

            return result; // Return the JSON array
        },

        // Capture the file when it is selected
        async onFileSelect(event) {
            const file = event.files[0]; // Get the first selected file
            if (file) {
                const reader = new FileReader();
                reader.onload = async (e) => {
                    const text = e.target.result;
                    const jsonData = await this.csvToJson(text); // Convert CSV to JSON
                    this.file_date = jsonData; // Store JSON data
                    this.file_to_upload = file; // Store the selected file
                    console.log('Converted JSON data:', this.file_date); // Debugging log
                };
                reader.readAsText(file); // Read the file as text
            }
        },

        // Trigger backend upload
        async uploadFile() {
            if (!this.file_to_upload) {
                alert("Please select a file before uploading.");
                return;
            }

            let url = this.ajax_url + '/bulk-import';

            // Convert the CSV data to JSON before making the request
            let param = this.file_date;

            let options = {
                method: 'POST', // Specify the method
                headers: {
                    'Content-Type': 'application/json', // Ensure JSON format is sent
                },
                params: param,
            };

            try {
                const response =await vaah().ajax(
                    url,
                    this.getItemAfter(),
                    options
                );

            } catch (error) {
                console.error('Error:', error);
            }
        },

        async exportAppointments() {
            try {
                // Fetch file data
                const res = await vaah().ajax(ajax_url + '/export');

                const blob = new Blob([res.data]);
                const url = window.URL.createObjectURL(blob);

                // Create a link and trigger the download
                const link = document.createElement('a');
                link.href = url;
                link.download = 'sample-appointments.csv'; // Set the download attribute
                document.body.appendChild(link);
                link.click();

                // Cleanup
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Error occurred while downloading the file:', error);
            }
        },

        // Tab View back and next functions --------------------
        moveToMappingTab(){
            this.tabs[0].disabled = true;
            this.tabs[1].disabled = false;
            this.activeTabIndex = 1;
        },

        moveToSuccess(){
            this.tabs[1].disabled = true;
            this.tabs[2].disabled = false;
            this.mapFormDataToRows();
            this.activeTabIndex = 2;
        },

        moveToUpload(){
            this.tabs[0].disabled = true;
            this.tabs[1].disabled = false;
            this.activeTabIndex = 0;
        },

        closeMoveToImport(){
            // Reset tabs to initial state
            this.tabs[0].disabled = false;
            this.tabs[1].disabled = true;
            this.tabs[2].disabled = true;

            //varible setting to null
            this.csv_headers = [];
            this.csv_data = [];
            this.form_data = [];
            this.table_data = [];
            this.response_errors = [];

            // Set the active tab back to the first tab
            this.activeTabIndex = 0;

            // Close the dialog
            this.visible = false;
        },

        uploadMore()
        {
            // Reset tabs to initial state
            this.tabs[0].disabled = false;
            this.tabs[1].disabled = true;
            this.tabs[2].disabled = true;

            //varible setting to null
            this.csv_headers = [];
            this.csv_data = [];
            this.form_data = [];
            this.table_data = [];
            this.response_errors = [];

            // Set the active tab back to the first tab
            this.activeTabIndex = 0;
        },

        onTabChange(e){
            this.activeTabIndex = e.index;
        },

        // Import CSV -----------------------------------------------------------

        getDataForHeader(header_selected) {
            if (header_selected) {
                let selected_header = header_selected['label'];

                let result = [];
                for (let i = 0; i < this.csv_data.length; i++) {
                    const row = this.csv_data[i];
                    if (row[selected_header] !== undefined) {
                        result.push(row[selected_header]);
                    }
                }
                return result.length > 0 ? result : null;
            }
            return null;
        },


        mapFormDataToRows() {

            this.table_data = []; // Empty the array to remove previous records

            // Determine the maximum number of rows from all selected fields
            const num_rows = Math.max(
                this.form_data.patient_name?.length || 0,
                this.form_data.patient_email?.length || 0,
                this.form_data.doctor_name?.length || 0,
                this.form_data.doctor_email?.length || 0,
                this.form_data.reason_for_visit?.length || 0,
                this.form_data.appointment_date?.length || 0,
                this.form_data.appointment_time?.length || 0
            );

            // Loop over the maximum number of rows and fill the table data
            for (let i = 0; i < num_rows; i++) {
                this.table_data.push({
                    patient_name: this.form_data.patient_name?.[i] || '', // Fallback to empty string if not selected
                    patient_email: this.form_data.patient_email?.[i] || '',
                    doctor_name: this.form_data.doctor_name?.[i] || '',
                    doctor_email: this.form_data.doctor_email?.[i] || '',
                    reason_for_visit: this.form_data.reason_for_visit?.[i] || '',
                    appointment_date: this.form_data.appointment_date?.[i] || '',
                    appointment_time: this.form_data.appointment_time?.[i] || '',
                });
            }
        },

        // Function to convert CSV to JSON
        async csvToJson(csv) {
            const lines = csv.split('\n');
            const headers = lines[0].split(',').map((header) => header.trim().replace(/^"|"$/g, ''));  // Remove quotes from headers

            const data = [];

            // Extract rows as objects
            for (let i = 1; i < lines.length; i++) {
                const currentline = lines[i].split(',').map((value) => value.trim().replace(/^"|"$/g, '')); // Remove quotes from values
                const obj = {};
                headers.forEach((header, index) => {
                    obj[header] = currentline[index];
                });
                data.push(obj);
            }

            return { headers, data };
        },

        // Capture the file when it is selected
        async onFileSelect(event) {
            const file = event.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = async (e) => {
                    const text = e.target.result;
                    const { headers, data } = await this.csvToJson(text); // Convert CSV to JSON
                    this.csv_headers = headers.map(header => ({ label: header, value: header }));  // Map headers for dropdowns
                    this.csv_data = data;  // Store CSV rows
                };
                reader.readAsText(file);
            }
        },

        async uploadFile() {
            if (this.csv_data && this.csv_data.length > 0) {
                this.moveToMappingTab();
            } else {
                alert("Please select file!");
            }
        },

        async submitData() {

            const patient_name = this.getDataForHeader(this.selected_patient_name);
            const patient_email = this.getDataForHeader(this.selected_patient_email);
            const doctor_name = this.getDataForHeader(this.selected_doctor_name);
            const doctor_email = this.getDataForHeader(this.selected_doctor_email);
            const reason_for_visit = this.getDataForHeader(this.selected_medical_concern);
            const appointment_date = this.getDataForHeader(this.selected_appointment_date);
            const appointment_time = this.getDataForHeader(this.selected_appointment_time);

            this.form_data = {
                patient_name,
                patient_email,
                doctor_name,
                doctor_email,
                reason_for_visit,
                appointment_date,
                appointment_time,
            };
            this.moveToSuccess();

        },

        async publishData()
        {
            let url = this.ajax_url + '/publish-imported-data';

            // Convert the CSV data to JSON before making the request
            let param = this.form_data;

            let options = {
                method: 'POST', // Specify the method
                headers: {
                    'Content-Type': 'application/json', // Ensure JSON format is sent
                },
                params: param,
            };

            try {
                const response =await vaah().ajax(
                    url,
                    null,
                    options
                );
                this.response_errors = [];
                this.response_errors = response.data.error;
                console.log(response.data.error);

            } catch (error) {
                console.error('Error:', error);
            }
        }


        //---------------------------------------------------------------------
    }
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useAppointmentStore, import.meta.hot))
}
