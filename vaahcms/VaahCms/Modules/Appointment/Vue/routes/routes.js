let routes= [];

import dashboard from "./vue-routes-dashboard";
import doctors from "./vue-routes-doctors";

routes = routes.concat(dashboard);
routes = routes.concat(doctors);

export default routes;
