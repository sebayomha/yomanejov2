import {RouterModule, Routes} from '@angular/router';
import { FreeClassFinderComponent } from './components/free-class-finder/free-class-finder.component';
import { PendingConfirmationSchedulesComponent } from './components/pending-confirmation-schedules/pending-confirmation-schedules.component';


const app_routes: Routes = [
  { path: 'buscarclase', component: FreeClassFinderComponent},
  { path: 'pendientes', component: PendingConfirmationSchedulesComponent},
  { path: '', pathMatch: 'full', redirectTo: 'buscarclase' },
  { path: '**', pathMatch: 'full', redirectTo: 'buscarclase' }
];

export const app_routing = RouterModule.forRoot(app_routes);
