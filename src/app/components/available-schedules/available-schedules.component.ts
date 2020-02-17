import { Component, Input, ViewChildren, QueryList, ViewChild } from '@angular/core';
import { MatSelectionList } from '@angular/material';
import { trigger,animate,transition,style } from '@angular/animations';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from 'src/app/models/response';
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
    @Input() student_phone: number;
    @Input() address: Array<any>;
    @Input() address_alternative: Array<any>;

    @ViewChildren(MatSelectionList) viewChildren !: QueryList<MatSelectionList>;
    @ViewChild('customModal') customModal;

    step:number;
    classes: Array<any>;
    order_information:any;
    currentCheckedValue:String;
    dataToConfirm = [];
    indexesClasses = [];
    cantSelectedClasses: number;

    constructor(private cronogramaService: CronogramaService) { }

    showMore(option) {
      option.showMoreHours = 20;
    }

    showLess(option) {
      option.showMoreHours = 4;
    }

    ngOnInit() {
      this.step = 0;
      this.cantSelectedClasses = 0;
      this.classes = [];        
      this.order_information = this.data;
    }

    ngOnChanges() {
      this.cantSelectedClasses = 0;
    }

    ngAfterViewInit() {
      $('[data-toggle="tooltip"]').tooltip();
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

    totalClassesSelected(selectedOptions, fecha, index, event){
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
        'horario': event.option.value.horaInicio,
        'id_auto': event.option.value.idAuto,
        'da': event.option.value.usandoDirAlt
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

      this.cantSelectedClasses = this.classes.reduce( (total, element) => {
        return total + element.cant;
      }, 0)
 
    }

    saveOptions(){
      this.dataToConfirm = [];
      this.dataToConfirm.push({'selected_options' : this.classes});
      this.dataToConfirm.push({'student_name' : this.student_name});
      this.dataToConfirm.push({'student_phone' : this.student_phone});
      this.dataToConfirm.push({'address' : this.address});
      this.dataToConfirm.push({'address_alternative' : this.address_alternative});
      this.customModal.open();
    }

    confirmSchedule($event) {
      this.cronogramaService.guardarCronograma($event).subscribe( (response: Response) => {
        console.log(response);
      })
      console.log($event);
    }

}