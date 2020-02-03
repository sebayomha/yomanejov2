import { Component, Input, ViewChildren, QueryList } from '@angular/core';
import { MatSelectionList, MatListOption } from '@angular/material';
import {SelectionModel} from '@angular/cdk/collections';
declare var $: any;

@Component({
  selector: 'available-schedules',
  templateUrl: './available-schedules.component.html',
  styleUrls: ['./available-schedules.component.scss', '../../global-styles.scss']
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
    cantSelectedClasses;

    constructor() { }

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