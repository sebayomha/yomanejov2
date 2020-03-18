import { Component, ChangeDetectorRef, Input, ViewChildren, QueryList, ViewChild, Output, EventEmitter, ɵConsole } from '@angular/core';
import { MatSelectionList } from '@angular/material';
import { trigger,animate,transition,style } from '@angular/animations';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from 'src/app/models/response';
import { Excepcion } from 'src/app/models/excepcion';
import { MatSnackBar } from '@angular/material/snack-bar';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
declare var $: any;

@Component({
  selector: 'available-schedules',
  templateUrl: './available-schedules.component.html',
  styleUrls: ['./available-schedules.component.scss', '../../global-styles.scss'],
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
    ]),
  ]
})

export class AvailableSchedulesComponent {

    @Input() data: any;
    @Input() number_of_classes: number;
    @Input() student_name: string;
    @Input() student_phone: string;
    @Input() disponibilidad: any;
    @Input() address: Array<any>;
    @Input() address_alternative: Array<any>;
    @Input() excepciones: Array<Excepcion>;
    @Input() edit_cronograma;

    @Output() finish = new EventEmitter<any>();
    @ViewChildren(MatSelectionList) viewChildren !: QueryList<MatSelectionList>;
    @ViewChild('customModal') customModal;

    step:number;
    classes: Array<any>;
    classes_send: Array<any>;
    order_information:any;
    currentCheckedValue:String;
    dataToConfirm = [];
    indexesClasses = [];
    cantSelectedClasses: number;
    showSuccessBanner: boolean = false;
    durationInSeconds = 3;
    idCronogramaGuardado: number;
    not_available_classes = [];
    fecha_no_disponible:boolean;
    show_info_banner:boolean = false;
    operationCustomModal: string;

    /* variables para editar cronograma */
    idAlumno;
    idExcepciones = [];
    idCronograma;
    idDisponibilidad;
    idDireccionPrincipal;
    idDireccionAlternativa;

    constructor(private cdr: ChangeDetectorRef, private cronogramaService: CronogramaService, private _snackBar: MatSnackBar) { }

    ngOnInit() {
      this.step = 0;
      this.cantSelectedClasses = 0;
      this.classes = [];        
      this.order_information = this.data;
      if (this.edit_cronograma) {
        this.idAlumno = this.edit_cronograma.alumno;
        this.idCronograma = this.edit_cronograma.idCronograma;
        this.idDireccionPrincipal = this.edit_cronograma.idDireccionPrincipal;
        this.idDireccionAlternativa = this.edit_cronograma.idDireccionAlternativa;
        this.idDisponibilidad = this.edit_cronograma.idDisponibilidad;

        if (this.edit_cronograma.excepciones[0].idExcepcion == null) {
          this.idExcepciones = null;
        } else {
          this.edit_cronograma.excepciones.forEach( excepcion => {
            this.idExcepciones.push(excepcion.idExcepcion);
          })
        }
      }
    }
    
    ngOnChanges() {
      this.cantSelectedClasses = 0;
      this.classes = []; 
      setTimeout( () => {
        if (this.edit_cronograma) {
          this.verificoClases();
          this.cdr.detectChanges();
        } 
      }, 0)
    }

    ngAfterViewInit() {
      $('[data-toggle="tooltip"]').tooltip();
    }

    // Editar cronograma:
    // Tomo las clases que se habian seleccionado y chequeo que aun esten disponibles. Las que no lo esten apareceran en el banner.
    verificoClases() {
      this.show_info_banner = false
      this.not_available_classes = [];
      this.classes = [];
      let index_class = 0;
      let index_opt = 0;
      this.edit_cronograma.clases.forEach(clase => {
        index_class += 1;
        let fecha_clase = clase.fecha;
        let hora_inicio = clase.horaInicio;
        let auto = clase.auto;
        index_opt += 1;
    
        this.data.forEach(opt => {

          if (opt.fecha == fecha_clase) {
            this.fecha_no_disponible = true;
            
            opt.horarios.forEach(opt_day => {

              if((opt_day.horaInicio == hora_inicio) && (opt_day.idAuto == auto)) {

                //Selecciono la clase dentro de las opciones de busqueda.
                this.fecha_no_disponible = false;
                this.cantSelectedClasses += 1;
                opt_day.selected = true;

                var option = {
                  'index': index_opt,
                  'cant': 1,
                  'fecha': opt.fecha,
                  'dia': opt.dia,
                  'fecha_orden': new Date(opt.fecha),
                  'horario': opt_day.horaInicio,
                  'id_auto': opt_day.idAuto,
                  'da': opt_day.usandoDirAlt,
                  'idZona': opt_day.idZona,
                  'idClase': clase.idClase
                }

                console.log("OPT:: ", opt);
                console.log("CLASE ::", clase);
                this.classes.push(option);

              }
              
            });
            
            if(this.fecha_no_disponible == true) {

              //Armo array del banner
              this.not_available_classes.push('La clase número '+index_class+' del día '+fecha_clase+' a las '+hora_inicio+' hs ya no se encuentra disponible.');
              this.show_info_banner = true;
            }
          }
        });
        
      });


      this.classes_send = [];
      this.classes.forEach(element => {
        if (element.cant == 1) {
          element.fecha_orden.setDate(element.fecha_orden.getDate() - 1);
          this.classes_send.push(element);
        }
      });

    }

    cerrarBanner() {
      this.show_info_banner = false;
    }

    showMore(option) {
      option.showMoreHours = 20;
    }

    showLess(option) {
      option.showMoreHours = 4;
    }

    setStep(index: number) {
      this.step = index;
    }

    nextStep() {
      this.step++;
    }

    prevStep() {
      this.step--;
    }

    totalClassesSelected(selectedOptions, fecha, index, event, dia){
      if (event.option.selected) {
        event.source.deselectAll();
        event.option._setSelected(true);
      } else {
        event.source.deselectAll();
      }

      var option = {
        'index': '',
        'cant': null,
        'fecha': fecha,
        'dia': dia,
        'fecha_orden': new Date(fecha),
        'horario': event.option.value.horaInicio,
        'id_auto': event.option.value.idAuto,
        'da': event.option.value.usandoDirAlt,
        'idZona': event.option.value.idZona,
        'idClase': null
      }

      if (selectedOptions._selection.size == 0) {
        if (!this.classes.some(e => e.index == index)) {
          option.index = index;
          option.cant = 0;
          this.classes.push(option)
        } else {
          let indexClass = this.classes.findIndex( element => element.index == index);
          this.classes[indexClass].cant = 0;
        }
      } else {
        if (!this.classes.some(e => e.index == index)) {
          option.index = index;
          option.cant = 1;
          this.classes.push(option)
        } else {
          let indexClass = this.classes.findIndex( element => element.index == index);
          this.classes[indexClass].cant = 1;
          this.classes[indexClass].horario = option.horario;
          this.classes[indexClass].id_auto = option.id_auto;
          this.classes[indexClass].da = option.da;
          this.classes[indexClass].fecha = option.fecha;
        }
      }

      this.cantSelectedClasses = 0;
      this.classes_send = [];
      this.classes.forEach(element => {
        this.cantSelectedClasses = this.cantSelectedClasses + element.cant;
        if (element.cant == 1) {
          element.fecha_orden.setDate(element.fecha_orden.getDate() - 1);
          this.classes_send.push(element);
        }
      });

      console.log("CLASSES SEND:: ", this.classes_send);

    }

    saveOptions(){
      this.dataToConfirm = [];
      //Ordeno los dias
      this.classes_send.sort(function(a, b){return a.fecha_orden - b.fecha_orden});
      console.log("classes_send ::", this.classes_send);
      this.dataToConfirm.push({'idCronograma' : this.idCronograma});
      this.dataToConfirm.push({'selected_options' : this.classes_send});
      this.dataToConfirm.push({'idAlumno' : this.idAlumno});
      this.dataToConfirm.push({'student_name' : this.student_name});
      this.dataToConfirm.push({'student_phone' : this.student_phone.replace(/\s/g, "").replace('-', "")});
      this.dataToConfirm.push({'idDireccionPrincipal' : this.idDireccionPrincipal});
      this.dataToConfirm.push({'address' : this.address});
      this.dataToConfirm.push({'idDireccionAlternativa' : this.idDireccionAlternativa});
      this.dataToConfirm.push({'address_alternative' : this.address_alternative});
      this.dataToConfirm.push({'idDisponibilidad' : this.idDisponibilidad});
      this.dataToConfirm.push({'disponibilidad' : this.disponibilidad});
      this.dataToConfirm.push({'idExcepciones' : this.idExcepciones});
      this.dataToConfirm.push({'excepciones' : this.excepciones});
      console.log("dataToConfirm ::", this.dataToConfirm);
      this.customModal.open();
    }

    confirmSchedule($event) {
      if (!this.edit_cronograma) {
        this.operationCustomModal = "GuardarCronograma";
        this.cronogramaService.guardarCronograma($event).subscribe( (response: Response) => {
          console.log(response);
          if (response.code == 0) {
            this.showSuccessBanner = true;
            this.idCronogramaGuardado = response.data;
          } else {
            this.idCronogramaGuardado = null;
            this.customModal.onClose();
            this.showSuccessBanner = false;
            this._snackBar.openFromComponent(SnackbarComponent, {
              duration: this.durationInSeconds * 1100,
              data: response
            });
          }
        })
      } else {
        this.operationCustomModal = "EditarCronograma";
        this.cronogramaService.actualizarCronogramaPendiente($event).subscribe( (response: Response) => {
          console.log(response);
          if (response.code == 0) {
            this.showSuccessBanner = true;
            this.idCronogramaGuardado = response.data;
          } else {
            this.idCronogramaGuardado = null;
            this.customModal.onClose();
            this.showSuccessBanner = false;
            this._snackBar.openFromComponent(SnackbarComponent, {
              duration: this.durationInSeconds * 1100,
              data: response
            });
          }
        })
      }

      console.log($event);
    }

    onCustomModalClose($event) {
      if (this.edit_cronograma) {
        this.finish.emit("EditarCronograma")
      } else {
        this.finish.emit("GuardarCronograma");
      }
    }

}