let routes= [];

import dashboard from "./vue-routes-dashboard";
import doctors from "./vue-routes-doctors";
import patients from "./vue-routes-patients";
import appointments from "./vue-routes-appointments";

routes = routes.concat(dashboard);
routes = routes.concat(doctors);
routes = routes.concat(patients);
routes = routes.concat(appointments);

export default routes;
