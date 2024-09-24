let routes= [];
let routes_list= [];

import List from '../pages/appointments/List.vue'
import Form from '../pages/appointments/Form.vue'
import Item from '../pages/appointments/Item.vue'

routes_list = {

    path: '/appointments',
    name: 'appointments.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'appointments.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'appointments.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

