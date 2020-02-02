import { Component, Input } from '@angular/core';

@Component({
  selector: 'available-schedules',
  templateUrl: './available-schedules.component.html',
  styleUrls: ['./available-schedules.component.scss', '../../global-styles.scss']
})

export class AvailableSchedulesComponent {


    @Input() data: any;
    @Input() number_of_classes: number;

    step:number;
    classes:number;
    order_information:any;
    currentCheckedValue:String;
    arraySelectedOptions:Array<any>;

    constructor() { }

    ngOnInit() {
        this.step = 0;
        this.order_information = this.data;
        console.log('LLEGO', this.data);

        //Recorrer todos los horarios y los pongo los estatus en false.
        this.order_information.forEach(option => {
          option.autos.forEach(element => {
            element.horarios.forEach(horario => {
              horario.checked = false;
              // if(horario.horaInicio == '08:00') {
              //   horario.checked = true;
              // } else { horario.checked = false; } 
            });
          });
        });
        console.log('FALSE', this.order_information);
        this.currentCheckedValue = null;
        this.classes = 0;
        
        // if (this.data) this.orderInformation();
        
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

    resetRadio(radio:any, index, horario){
      setTimeout(() => {

          //Recorrer los arrays de horarios del grupo seleccionado
          this.order_information[index].autos.forEach(option => {
            option.horarios.forEach(horario => {
              horario.checked = false;
            });
          });

          if (this.currentCheckedValue && radio.value == this.currentCheckedValue) {
            radio.checked = false;
            horario.checked = false;

            this.currentCheckedValue = null;
            this.order_information[index].opcion_selecionada = '';
            console.log('INFO:', this.order_information);
            this.classes = this.classes - 1;


          } else {
            this.currentCheckedValue = radio.value;
            horario.checked = true;
            this.order_information[index].opcion_selecionada = this.currentCheckedValue;
            console.log('INFO:', this.order_information);
            this.classes = this.classes + 1;
          }
      })
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