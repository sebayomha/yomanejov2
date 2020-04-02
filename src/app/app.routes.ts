import { RouterModule, Routes } from '@angular/router';
import { FreeClassFinderComponent } from './components/free-class-finder/free-class-finder.component';
import { PendingConfirmationSchedulesComponent } from './components/pending-confirmation-schedules/pending-confirmation-schedules.component';
import { LessonsComponent } from './components/lessons/lessons.component';
import { StudentsComponent } from './components/students/students.component';
import { EditarAlumnoComponent } from './components/editar-alumno/editar-alumno.component';
import { AddClassSchedulesComponent } from './components/add-class-schedules/add-class-schedules.component';

const app_routes: Routes = [
  { path: 'busqueda', component: FreeClassFinderComponent},
  { path: 'pendientes', component: PendingConfirmationSchedulesComponent,  data: {animation: 'StudentsPage'}},
  { path: 'pendientes/:idCronograma', component: PendingConfirmationSchedulesComponent},
  { path: 'pendientes/agregarClase/:idCronograma', component: AddClassSchedulesComponent, data: {animation: 'EditingStudentPage'}},
  { path: 'clases', component: LessonsComponent},
  { path: 'alumnos', component: StudentsComponent, data: {animation: 'StudentsPage'}},
  { path: 'alumnos/editar/:id', component: EditarAlumnoComponent, data: {animation: 'EditingStudentPage'}},
  { path: '', pathMatch: 'full', redirectTo: 'busqueda' },
  { path: '**', pathMatch: 'full', redirectTo: 'busqueda' }
];

export const app_routing = RouterModule.forRoot(app_routes);
