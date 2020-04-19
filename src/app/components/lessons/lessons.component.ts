import { Component, ViewChild } from '@angular/core';
import { BreakpointObserver } from '@angular/cdk/layout';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from '../../models/response';
import { trigger,animate,transition,style } from '@angular/animations';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { MatSnackBar } from '@angular/material';
import { AppSettings } from '../../appConstants';

@Component({
  selector: 'lessons',
  templateUrl: './lessons.component.html',
  styleUrls: ['./lessons.component.scss'],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: '0' }),
        animate('.5s ease-out', style({ opacity: '1' })),
      ]),
      transition(':leave', [
        style({ opacity: '1' }),
        animate('.5s ease-out', style({ opacity: '0' })),
      ])
    ])]
})

export class  LessonsComponent {

  constructor(private breakpointObserver: BreakpointObserver, private cronogramaService: CronogramaService, private _snackBar: MatSnackBar) { }
  USER_ROLE = AppSettings.USER_ROLE;

  @ViewChild('customModal') customModal;

  displayedColumns: string[] = this.USER_ROLE == 'ADMIN' ? ['No', 'numero', 'hora', 'direccion', 'alumno', 'operacion'] : ['No', 'numero', 'hora', 'direccion', 'alumno'];
  select_day : Date = new Date();
  minDate = new Date();
  autos;
  dataToConfirm: any;
  durationInSeconds = 3;
  

  ngOnInit() {
    this.cronogramaService.obtenerClasesPorFecha(this.formatDate()).subscribe( (response: Response) => {
      console.log(response.data)
      this.autos = Object.entries(response.data);
      this.obtenerClasesPorRealizarse(this.autos);
      this.obtenerClasesRealizadas(this.autos);
    })
  }

  buscarClases($event) {
    this.cronogramaService.obtenerClasesPorFecha(this.formatDateWithDate($event.value)).subscribe( (response: Response) => {
      this.autos = Object.entries(response.data);
      this.obtenerClasesPorRealizarse(this.autos);
      this.obtenerClasesRealizadas(this.autos);
      console.log(response);
    })
  }

  showMore(auto, operation) {
    if (operation == 'clasesRealizadas') {
      auto.clasesRealizadas = auto.clasesRealizadasAll;
    } else {
      auto.clasesPorRealizarse = auto.clasesPorRealizarseAll;
    }
  }

  showLess(auto, operation) {
    if (operation == 'clasesRealizadas') {
      auto.clasesRealizadas = auto.clasesRealizadasAll.splice(0,4);
    } else {
      auto.clasesPorRealizarse = auto.clasesPorRealizarseAll.splice(0,4);
    }
  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }

  formatDate() {
    var d = new Date(),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
  }

  formatDateWithDate(date: Date) {
    var d = date,
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
  }

  obtenerClasesRealizadas(autos) {
    const today = new Date();

    autos.forEach( auto => {
      auto.clasesRealizadas = auto[1].filter( clase => {
        if (clase.idClase == null) {
          return false;
        }
        var horaInicioDate = new Date(clase.fecha.replace('-', '/'));
        horaInicioDate.setHours(clase.horaInicio.split(':')[0],clase.horaInicio.split(':')[1],0);
        if (today >= horaInicioDate) {
          return true;
        }
        return false;
      })
      auto.clasesRealizadasAll = auto.clasesRealizadas;
      auto.clasesRealizadas = auto.clasesRealizadas;
    })
  }

  obtenerClasesPorRealizarse(autos) {  
    const today = new Date();
    autos.forEach( auto => {
      auto.clasesPorRealizarse = auto[1].filter( clase => {
        if (clase.idClase == null) {
          return false;
        }
        var horaInicioDate = new Date(clase.fecha.replace('-', '/'));
        horaInicioDate.setHours(clase.horaInicio.split(':')[0],clase.horaInicio.split(':')[1],0);
        if (today < horaInicioDate) {
          return true;
        }
        return false;
      })
      auto.clasesPorRealizarseAll = auto.clasesPorRealizarse;
      auto.clasesPorRealizarse = auto.clasesPorRealizarse;
    })
  }

  // editarClase(idAlumno){
  //   this.cronogramaService.obtenerClasesDisponiblesParaAlumno(idAlumno).subscribe( (response: Response) => {
  //     console.log('RESPUESTA ',response);
  //   })
  // }

  //Funcion que agrega el motivo por el cual la clase no se produjo.
  cancelarClase(clase){

    this.dataToConfirm = {
      'idClase': clase.idClase,
      'fecha': clase.fecha,
      'horaInicio': clase.horaInicio
    };

    this.customModal.open();

  }

  confirmUnsubscribe(data){

    this.cronogramaService.cancelarClase(data.idClase, data.motivoDeBaja).subscribe( (response: Response) => {

      this.customModal.onClose();
      this.cronogramaService.obtenerClasesPorFecha(this.formatDateWithDate(this.select_day)).subscribe( (response: Response) => {
        this.autos = Object.entries(response.data);
        this.obtenerClasesPorRealizarse(this.autos);
        this.obtenerClasesRealizadas(this.autos);
      })
      this._snackBar.openFromComponent(SnackbarComponent, {
        duration: this.durationInSeconds * 1100,
        data: response
      });
    })

    this.customModal.onClose();
  }

  onCustomModalClose($event) {
    this.customModal.onClose();
  }

}