import { Component, OnInit, Output, EventEmitter, ViewChild } from '@angular/core';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from '../../models/response';
import { BreakpointObserver } from '@angular/cdk/layout';
import { MatSnackBar } from '@angular/material/snack-bar';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { ActivatedRoute,Router } from '@angular/router';
import { AlumnosService } from 'src/app/services/alumnos/alumnos.service';

@Component({
  selector: 'app-pending-confirmation-schedules',
  templateUrl: './pending-confirmation-schedules.component.html',
  styleUrls: ['./pending-confirmation-schedules.component.css']
})
export class PendingConfirmationSchedulesComponent implements OnInit {

  constructor(private router: Router, private alumnoService: AlumnosService, private route: ActivatedRoute, private cronogramaService: CronogramaService, private breakpointObserver: BreakpointObserver, private _snackBar: MatSnackBar) { }

  cronogramas: Array<any> = [];
  cronograma_edit = [];
  displayedColumns: string[] = ['noClase', 'fecha', 'hora', 'direccion', 'auto'];
  showSuccessBanner: boolean = false;
  dataToConfirm: any;
  durationInSeconds: number = 3;
  operation: string;
  show_edit:boolean = false;
  direccionDocumento: any = {
    direccion: '',
    documento: ''
  };

  @Output() finish = new EventEmitter<any>();
  @ViewChild('customModal') customModal;

  isLoaded = false;
  idCronograma;
  detailedCronograma;
  sub;

  ngOnInit() {
    this.cronogramaService.obtenerCronogramasPendientesDeConfirmar().subscribe( (response: Response) => {
      this.cronogramas = response.data;
      console.log("cronogramas", this.cronogramas);
      this.isLoaded = true;

      
      this.sub = this.route.params.subscribe(params => {
        this.idCronograma = +params['idCronograma'];
        this.detailedCronograma = this.getDetailedCronograma(this.idCronograma);
        console.log("idCronograma::", this.idCronograma)
        console.log("detailedCronograma:: ", this.detailedCronograma);
        //ESTO VA A SER USADO PARA CUANDO SE PUEDAN FILTRAR LOS CRONOGRAMAS CON UN BUSCADOR ARRIBA
    });
    })
  }

  getDetailedCronograma(idCronograma) {
    console.log("cronos: ", this.cronogramas)
    return this.cronogramas.find( (cronograma) => {
      if (cronograma.idCronograma == idCronograma) {
        return true;
      }
    })
  }

  ngOnDestroy() {
    this.sub.unsubscribe();
  }

  verTodosCronogramas() {
    this.detailedCronograma = null;
    this.idCronograma = null;
    this.router.navigate(['pendientes']);
  }

  inicialesNombreAlumno(nombreAlumno: string) {
    let nombreAlumnoArr = nombreAlumno.split(' ');
    let iniciales: string = '';
    nombreAlumnoArr.forEach(element => {
      iniciales += element[0];
    });

    return iniciales;
  }

  getHourAndMinutes(cronograma) {
    let endDate = new Date(cronograma.fechaHoraGuardado);
    let purchaseDate = new Date();
    let diffMs = Math.abs((purchaseDate.getTime() - endDate.getTime())); // milliseconds
    let diffDays = Math.floor(diffMs / 86400000); // days
    let diffHrs = Math.floor((diffMs % 86400000) / 3600000); // hours
    let diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
    return diffHrs + " horas " + diffMins + " minutos";
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
      'documento': '',
      'id_DirFisica': '',
      'id_DirPrincipal': '',
      'id_DirAlternativa': '',
      'calle_DirFisica': '',
      'calle_diag_DirFisica': '',
      'calle_a_DirFisica': '',
      'calle_a_diag_DirFisica': '',
      'calle_b_DirFisica': '',
      'calle_b_diag_DirFisica': '',
      'ciudad_DirFisica': '',
      'numero_DirFisica': '',
      'floor_DirFisica': '',
      'observaciones_DirFisica': ''
    };
    this.operation = 'Confirmar';

    this.alumnoService.getInformacionPersonal(this.dataToConfirm.idAlumno).subscribe( (response:Response) => {
      if (response.code == 0) {
        this.direccionDocumento.direccion = response.data;
        this.dataToConfirm.documento = response.data[0].documento;
        this.setDireccionData(response.data[0]);
        this.customModal.setDireccionDefault();
        this.customModal.open();
      } 
    })
  }

  setDireccionData(responseData) {
    this.dataToConfirm.id_DirFisica =responseData.id_DirFisica;
    this.dataToConfirm.id_DirPrincipal = responseData.id_DirPrincipal;
    this.dataToConfirm.id_DirAlternativa = responseData.id_DirAlternativa;
    this.dataToConfirm.calle_DirFisica = responseData.calle_DirFisica;
    this.dataToConfirm.calle_diag_DirFisica = responseData.calle_diag_DirFisica;
    this.dataToConfirm.calle_a_DirFisica = responseData.calle_a_DirFisica;
    this.dataToConfirm.calle_a_diag_DirFisica = responseData.calle_a_diag_DirFisica;
    this.dataToConfirm.calle_b_DirFisica = responseData.calle_b_DirFisica;
    this.dataToConfirm.calle_b_diag_DirFisica = responseData.calle_b_diag_DirFisica;
    this.dataToConfirm.ciudad_DirFisica = responseData.ciudad_DirFisica;
    this.dataToConfirm.floor_DirFisica = responseData.floor_DirFisica;
    this.dataToConfirm.observaciones_DirFisica = responseData.observaciones_DirFisica;
    this.dataToConfirm.numero_DirFisica = responseData.numero_DirFisica;
    this.dataToConfirm.departamento_DirFisica = responseData.departamento_DirFisica;
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

  onEditSchedule(index){
    this.show_edit = true;
    this.cronograma_edit = this.cronogramas[index];
  }

  //Cierro edicion
  closeEditCrono(flag){
    this.show_edit = flag;
  }
}
