import { RouterModule, Routes } from '@angular/router';
import { FreeClassFinderComponent } from './components/free-class-finder/free-class-finder.component';
import { PendingConfirmationSchedulesComponent } from './components/pending-confirmation-schedules/pending-confirmation-schedules.component';
import { LessonsComponent } from './components/lessons/lessons.component';
import { StudentsComponent } from './components/students/students.component';
import { EditarAlumnoComponent } from './components/editar-alumno/editar-alumno.component';
import { AddClassSchedulesComponent } from './components/add-class-schedules/add-class-schedules.component';
import { CarsComponent } from './components/cars/cars.component';
import { LoginComponent } from './components/login/login.component';
import { AuthGuardService } from './services/authGuard/auth-guard.service';
import { ChangePasswordGuardService } from './services/forgotPasswordGuard/change-password-guard.service';
import { ProfileComponent } from './components/profile/profile.component';
import { ChangePasswordComponent } from './components/change-password/change-password.component';

const app_routes: Routes = [
  { path: 'busqueda', component: FreeClassFinderComponent, canActivate:[AuthGuardService]},
  { path: 'pendientes', component: PendingConfirmationSchedulesComponent,  data: {animation: 'StudentsPage'}, canActivate:[AuthGuardService]},
  { path: 'pendientes/:idCronograma', component: PendingConfirmationSchedulesComponent},
  { path: 'pendientes/agregarClase/:idCronograma', component: AddClassSchedulesComponent, data: {animation: 'EditingStudentPage'}, canActivate:[AuthGuardService]},
  { path: 'clases', component: LessonsComponent, canActivate:[AuthGuardService]},
  { path: 'perfil', component: ProfileComponent, data: {animation: 'StudentsPage'}, canActivate:[AuthGuardService]},
  { path: 'perfil/cambiarContraseña', component: ChangePasswordComponent, data: {animation: 'EditingStudentPage'}, canActivate:[AuthGuardService]},
  { path: 'cambiarContraseña/:id', component: ChangePasswordComponent, canActivate:[ChangePasswordGuardService]},
  { path: 'autos', component: CarsComponent, data: {authRole: 'ADMIN'}, canActivate:[AuthGuardService]},
  { path: 'alumnos', component: StudentsComponent, data: {animation: 'StudentsPage', authRole: 'ADMIN'}, canActivate:[AuthGuardService]},
  { path: 'alumnos/editar/:id', component: EditarAlumnoComponent, data: {animation: 'EditingStudentPage', authRole: 'ADMIN'}, canActivate:[AuthGuardService]},
  { path: 'login', component: LoginComponent, data: {isLoginPage: true}, canActivate:[AuthGuardService]},
  { path: '', pathMatch: 'full', redirectTo: 'busqueda'},
  { path: '**', pathMatch: 'full', redirectTo: 'busqueda' }
];

export const app_routing = RouterModule.forRoot(app_routes);