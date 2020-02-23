import {RouterModule, Routes} from '@angular/router';
import { FreeClassFinderComponent } from './components/free-class-finder/free-class-finder.component';
import { PendingConfirmationSchedulesComponent } from './components/pending-confirmation-schedules/pending-confirmation-schedules.component';


const app_routes: Routes = [
  { path: 'busqueda', component: FreeClassFinderComponent},
  { path: 'pendientes', component: PendingConfirmationSchedulesComponent},
  { path: '', pathMatch: 'full', redirectTo: 'busqueda' },
  { path: '**', pathMatch: 'full', redirectTo: 'busqueda' }
];

export const app_routing = RouterModule.forRoot(app_routes);