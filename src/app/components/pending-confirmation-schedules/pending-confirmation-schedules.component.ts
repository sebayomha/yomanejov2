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
  operation: string;
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

  onConfirmSchedule(clases, idCronograma, nombreAlumno, idAlumno, direccionPrincipal, direccionAlternativa, direccionPrincipalFormated, direccionAlternativaFormated) {
    let addressesAlumno = [];

    let direccionObject = {
      'idDireccion': direccionPrincipal,
      'direccionFormated': direccionPrincipalFormated
    }
    addressesAlumno.push(direccionObject);

    let direccionObjectCopy = Object.assign({}, direccionObject);

    if (direccionAlternativa != null) {
      direccionObjectCopy.idDireccion = direccionAlternativa;
      direccionObjectCopy.direccionFormated = direccionAlternativaFormated;
  
      addressesAlumno.push(direccionObjectCopy);
    }

    this.dataToConfirm = {
      'clases': clases,
      'idCronograma': idCronograma,
      'nombreAlumno': nombreAlumno,
      'idAlumno': idAlumno,
      'addressesAlumno': addressesAlumno,
      'documento': ''
    };
    this.operation = 'Confirmar';
    this.customModal.open();
  }

  confirmSchedule($event) {
    if (this.operation == 'Confirmar') {
      this.confirmarCronograma($event.idCronograma, $event.idAlumno, $event.direccionFisicaInformation, $event.documento, $event.clases);
    } else {
      this.eliminarCronograma($event.idCronograma, $event.idAlumno);
    }
  }

  onCustomModalClose() {
    this.customModal.onClose();
  }

  confirmarCronograma(idCronograma, idAlumno, direccionFisicaInformation, documento, clases) {
    this.cronogramaService.confirmarCronogramaPendiente(idCronograma, idAlumno, direccionFisicaInformation, documento, clases).subscribe( (response: Response) => { 
      if (response.code == 2) {
        response.data = "Para poder confirmar el cronograma debe modificar las siguientes clases ya que alguno de sus datos fue confirmado previamente: " + response.data.join(', ');
      }
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

  eliminarCronograma(idCronograma, idAlumno) {
    this.cronogramaService.cancelarCronogramaPendiente(idCronograma, idAlumno).subscribe( (response: Response) => {
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

  onCancelSchedule(idCronograma, nombreAlumno, idAlumno) {
    this.dataToConfirm = {
      'idCronograma': idCronograma,
      'nombreAlumno': nombreAlumno,
      'idAlumno': idAlumno
    };
    this.operation = 'Cancelar';
    this.customModal.open();
  }
}
