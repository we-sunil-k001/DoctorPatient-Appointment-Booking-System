let routes= [];

import dashboard from "./vue-routes-dashboard";
import doctors from "./vue-routes-doctors";
import patients from "./vue-routes-patients";

routes = routes.concat(dashboard);
routes = routes.concat(doctors);
routes = routes.concat(patients);

export default routes;
