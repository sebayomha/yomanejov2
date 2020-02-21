import { Component, OnInit, Output, EventEmitter, ViewChild } from '@angular/core';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from '../../models/response';
import { BreakpointObserver } from '@angular/cdk/layout';
import { MatSnackBar } from '@angular/material/snack-bar';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';

@Component({
  selector: 'app-pending-confirmation-schedules',
  templateUrl: './pending-confirmation-schedules.component.html',
  styleUrls: ['./pending-confirmation-schedules.component.css']
})
export class PendingConfirmationSchedulesComponent implements OnInit {

  constructor(private cronogramaService: CronogramaService, private breakpointObserver: BreakpointObserver, private _snackBar: MatSnackBar) { }

  cronogramas: Array<any>;
  displayedColumns: string[] = ['noClase', 'fecha', 'hora', 'direccion', 'auto'];
  showSuccessBanner: boolean = false;
  dataToConfirm: any;
  durationInSeconds: number = 3;
  @Output() finish = new EventEmitter<any>();
  @ViewChild('customModal') customModal;

  ngOnInit() {
    this.cronogramaService.obtenerCronogramasPendientesDeConfirmar().subscribe( (response: Response) => {
      this.cronogramas = response.data;
      console.log(response);
    })
  }

  inicialesNombreAlumno(nombreAlumno: string) {
    let nombreAlumnoArr = nombreAlumno.split(' ');
    let iniciales: string = '';
    nombreAlumnoArr.forEach(element => {
      iniciales += element[0];
    });

    return iniciales;
  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }

  sendWsp(numeroTelefono: string) {
    numeroTelefono = numeroTelefono.replace(/\s/g, "").replace('-', "");
    if (this.isMobile()) {
      window.open("https://wa.me/54"+numeroTelefono, "_blank");
    }
    else {
      window.open("https://web.whatsapp.com/send?phone=+54"+numeroTelefono, "_blank");
    } 
    console.log("SENDWSP")
  }

  onConfirmSchedule(idCronograma, nombreAlumno, idAlumno) {
    this.dataToConfirm = {
      'idCronograma': idCronograma,
      'nombreAlumno': nombreAlumno,
      'idAlumno': idAlumno
    };
    this.customModal.open();
  }

  confirmSchedule($event) {
    this.cronogramaService.confirmarCronogramaPendiente($event.idCronograma, $event.idAlumno).subscribe( (response: Response) => {
      this.showSuccessBanner = false;
      this.customModal.onClose();
      this._snackBar.openFromComponent(SnackbarComponent, {
        duration: this.durationInSeconds * 1100,
        data: response
      });
      this.ngOnInit();
      window.scrollTo(0, 0);
    })
  }

  onCustomModalClose() {
    this.customModal.onClose();
  }
}
