import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
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
import { MatNativeDateModule } from '@angular/material';
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


/* Services */
import { LoaderService } from './services/loader/loader-service.service';
import { RequestInterceptorService } from './services/interceptor/request-interceptor.service';

/* Pipes */
import { PhonePipe } from './pipes/address.model';
import { CapitalizePipe } from './pipes/capitalize.pipe';
registerLocaleData(localeEsAr);

// import { BarRatingModule } from "ngx-bar-rating";

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
    NavbarComponent 
  ],
  imports: [
    BrowserModule,
    HttpClientModule,
    BrowserAnimationsModule,
    MatCardModule,
    MatInputModule,
    FormsModule,
    MatTabsModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatCheckboxModule,
    MatSelectModule,
    MatButtonModule,
    MatExpansionModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatRadioModule,
    MatListModule,
    MatTableModule,
    app_routing
    
    // BarRatingModule
  ],
  providers:[DatePipe, {provide: LOCALE_ID, useValue: "es-AR"}, LoaderService, { provide: HTTP_INTERCEPTORS, useClass: RequestInterceptorService, multi: true }],
  bootstrap: [AppComponent],
  entryComponents: [
    SnackbarComponent
]
})
export class AppModule {}
