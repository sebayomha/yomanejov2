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

    constructor(private cdr : ChangeDetectorRef, private cronogramaService: CronogramaService, private _snackBar: MatSnackBar) { }

    ngOnInit() {
      this.step = 0;
      this.cantSelectedClasses = 0;
      this.classes = [];        
      this.order_information = this.data;
    }
    
    ngOnChanges() {
      this.cantSelectedClasses = 0;
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

      this.not_available_classes = [];
      let index_class = 0;
      this.edit_cronograma.clases.forEach(clase => {
        index_class += 1;
        let fecha_clase = clase.fecha;
        let hora_inicio = clase.horaInicio;
        let auto = clase.auto;

        this.data.forEach(opt => {
          if (opt.fecha == fecha_clase) {
            this.fecha_no_disponible = true;
            opt.horarios.forEach(opt_day => {
                if((opt_day.horaInicio == hora_inicio) && (opt_day.idAuto == auto)) {

                  //Selecciono la clase dentro de las opciones de busqueda.
                  this.fecha_no_disponible = false;
                  opt_day.selected = true;
                  this.cantSelectedClasses += 1;
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
        'idZona': event.option.value.idZona
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

      console.log(this.classes_send);

    }

    saveOptions(){
      this.dataToConfirm = [];
      //Ordeno los dias
      this.classes_send.sort(function(a, b){return a.fecha_orden - b.fecha_orden});
      console.log(this.classes_send)
      this.dataToConfirm.push({'selected_options' : this.classes_send});
      this.dataToConfirm.push({'student_name' : this.student_name});
      this.dataToConfirm.push({'student_phone' : this.student_phone.replace(/\s/g, "").replace('-', "")});
      this.dataToConfirm.push({'address' : this.address});
      this.dataToConfirm.push({'address_alternative' : this.address_alternative});
      this.dataToConfirm.push({'disponibilidad' : this.disponibilidad});
      this.dataToConfirm.push({'excepciones' : this.excepciones});
      this.customModal.open();
    }

    confirmSchedule($event) {
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
      console.log($event);
    }

    onCustomModalClose($event) {
      this.finish.emit($event);
    }

}