import { BrowserModule } from '@angular/platform-browser';
import { NgModule, Injectable } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';
import { DatePipe, registerLocaleData } from '@angular/common';
import { LOCALE_ID } from '@angular/core';
import { HTTP_INTERCEPTORS } from '@angular/common/http';
import localeEsAr from '@angular/common/locales/es-AR';
import { app_routing } from "./app.routes";

/* Material Components */
import { MatCardModule } from '@angular/material/card';
import { MatInputModule } from '@angular/material/input';
import { FormsModule } from '@angular/forms';
import { MatTabsModule } from '@angular/material/tabs';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBarModule } from '@angular/material/snack-bar';
import 'hammerjs';
import {MatRadioModule} from '@angular/material/radio';
import {MatListModule} from '@angular/material/list';
import {MatTableModule} from '@angular/material/table';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {MatSidenavModule} from '@angular/material/sidenav';
import {MatIconModule} from '@angular/material/icon';
import {MatStepperModule} from '@angular/material/stepper';
import {ReactiveFormsModule} from '@angular/forms';
import {MatTooltipModule} from '@angular/material/tooltip';
import {MatChipsModule} from '@angular/material/chips';
import {MatDialogModule} from '@angular/material/dialog';
import {MatToolbarModule} from '@angular/material/toolbar';

/* Components */
import { AppComponent } from './app.component';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { FreeClassFinderComponent } from './components/free-class-finder/free-class-finder.component';
import { LoaderComponent } from './components/loader/loader/loader.component';
import { AvailableSchedulesComponent } from './components/available-schedules/available-schedules.component';
import { ScheduleTooltipComponent } from './components/schedule-tooltip/schedule-tooltip.component';
import { CustomModalComponent } from './components/custom-modal/custom-modal.component';
import { LessonsComponent } from './components/lessons/lessons.component';
import { SnackbarComponent } from './components/snackbar/snackbar/snackbar.component';
import { PendingConfirmationSchedulesComponent } from './components/pending-confirmation-schedules/pending-confirmation-schedules.component';
import { NavbarComponent } from './components/navbar/navbar.component';
import { DireccionFisicaComponent } from './components/direccion-fisica/direccion-fisica.component';
import { EditPendingConfSchedulesComponent } from './components/edit-pending-conf-schedules/edit-pending-conf-schedules.component';
import { RouterModule } from '@angular/router';

/* Services */
import { LoaderService } from './services/loader/loader-service.service';
import { RequestInterceptorService } from './services/interceptor/request-interceptor.service';

/* Pipes */
import { PhonePipe } from './pipes/address.model';
import { CapitalizePipe } from './pipes/capitalize.pipe';
import { DocumentoPipe } from './pipes/documento.pipe';
import { DocumentoEditPipe } from './pipes/documentoEdit.pipe';

registerLocaleData(localeEsAr);

import { PasswordStrengthMeterModule } from 'angular-password-strength-meter';

import { HAMMER_GESTURE_CONFIG, HammerGestureConfig } from '@angular/platform-browser';
import { StudentsComponent } from './components/students/students.component';
import { StudentsDetailsComponent } from './components/students-details/students-details.component';
import { EditarAlumnoComponent } from './components/editar-alumno/editar-alumno.component';

import { SharedService } from './services/sharedService/shared-service';
import { AddClassSchedulesComponent } from './components/add-class-schedules/add-class-schedules.component';
import { CarsComponent } from './components/cars/cars.component';
import { LoginComponent } from './components/login/login.component';
import { ProfileComponent } from './components/profile/profile.component';
import { ChangePasswordComponent } from './components/change-password/change-password.component';
import { ServiceWorkerModule } from '@angular/service-worker';
import { environment } from '../environments/environment';

@Injectable()
export class MyHammerConfig extends HammerGestureConfig {
    overrides = <any> {
        'pinch': { enabled: false },
        'rotate': { enabled: false }
    }
}

@NgModule({
  declarations: [
    AppComponent,
    FreeClassFinderComponent,
    LoaderComponent,
    AvailableSchedulesComponent,
    SnackbarComponent,
    ScheduleTooltipComponent,
    CustomModalComponent,
    LessonsComponent,
    PendingConfirmationSchedulesComponent,
    PhonePipe,
    CapitalizePipe,
    DocumentoPipe,
    DocumentoEditPipe,
    NavbarComponent,
    DireccionFisicaComponent,
    StudentsComponent,
    StudentsDetailsComponent,
    EditarAlumnoComponent,
    EditPendingConfSchedulesComponent,
    AddClassSchedulesComponent,
    CarsComponent,
    LoginComponent,
    ProfileComponent,
    ChangePasswordComponent
  ],
  imports: [
    BrowserModule,
    HttpClientModule,
    BrowserAnimationsModule,
    MatCardModule,
    MatInputModule,    
    FormsModule,
    RouterModule,
    MatTooltipModule,
    ReactiveFormsModule,
    FormsModule,
    PasswordStrengthMeterModule,
    MatSidenavModule,
    MatTabsModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatCheckboxModule,
    MatDialogModule,
    MatToolbarModule,
    MatSelectModule,
    MatStepperModule,
    MatChipsModule,
    MatButtonModule,
    MatExpansionModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatRadioModule,
    MatListModule,
    MatTableModule,
    MatAutocompleteModule,
    app_routing,
    ServiceWorkerModule.register('ngsw-worker.js', { enabled: environment.production })
  ],
  providers:[DatePipe, {provide: LOCALE_ID, useValue: "es-AR"}, LoaderService, { provide: HTTP_INTERCEPTORS, useClass: RequestInterceptorService, multi: true }, {
    provide: HAMMER_GESTURE_CONFIG,
    useClass: MyHammerConfig
}, SharedService, MatDatepickerModule],
  bootstrap: [AppComponent],
  entryComponents: [
    SnackbarComponent
]
})
export class AppModule {}
