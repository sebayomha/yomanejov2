import { Component, Input, ViewChildren, QueryList, } from '@angular/core';
import { MatSelectionList, MatListOption } from '@angular/material';
import { trigger,animate,transition,style } from '@angular/animations';

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

    @ViewChildren(MatSelectionList) viewChildren !: QueryList<MatSelectionList>;

    step:number;
    classes: Array<any>;
    order_information:any;
    currentCheckedValue:String;
    arraySelectedOptions:Array<any>;
    indexesClasses = [];
    cantSelectedClasses: number;

    constructor() { }

    showMore(option) {
      option.showMoreHours = 12;
    }

    showLess(option) {
      option.showMoreHours = 4;
    }

    ngOnInit() {
        this.step = 0;
        this.cantSelectedClasses = 0;
        this.classes = [];        
        this.order_information = this.data;
        console.log('LLEGO', this.data);
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

    totalClassesSelected(selectedOptions, index, event){

      if (event.option.selected) {
        event.source.deselectAll();
        event.option._setSelected(true);
      } else {
        event.source.deselectAll();
      }


      var option = {
        'index': '',
        'cant': null
      }
      if (selectedOptions._selection.size == 0) {
        if (!this.classes.some(e => e.index == index)) {
          option.index = index;
          option.cant = 0;
          this.classes.push(option)
        } else {
          this.classes[this.classes.findIndex( element => element.index == index)].cant = 0;
        }
      } else {
        if (!this.classes.some(e => e.index == index)) {
          option.index = index;
          option.cant = 1;
          this.classes.push(option)
        } else {
          this.classes[this.classes.findIndex( element => element.index == index)].cant = 1;
        }
      }

      this.cantSelectedClasses = this.classes.reduce( (total, element) => {
        return total + element.cant;
      }, 0)
 
    }

    saveOptions(){

      this.arraySelectedOptions = [];

      this.order_information.forEach(option => {
        option.autos.forEach(element => {
          element.horarios.forEach(horario => {
            if(horario.checked == true){
              this.arraySelectedOptions.push({day: option.fecha, hora:horario.horaInicio, auto: element.idAuto});
            }
          });
        });
      });

      console.log('ARRAY:', this.arraySelectedOptions);

    }

}