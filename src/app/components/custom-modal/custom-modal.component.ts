import { Component, Input, Output, EventEmitter, ViewChild, ChangeDetectorRef, ViewChildren } from '@angular/core';
import { BreakpointObserver } from '@angular/cdk/layout';
import { DireccionFisicaComponent } from '../direccion-fisica/direccion-fisica.component';
import { NgForm } from '@angular/forms';
declare var $:any;

@Component({
  selector: 'custom-modal',
  templateUrl: './custom-modal.component.html',
  styleUrls: ['./custom-modal.component.scss']
})

export class CustomModalComponent {

  @Output() confirmation = new EventEmitter<string>();
  @Output() close = new EventEmitter<string>();
  @Input() data: any;
  @Input() component: string;
  @Input() successBanner: boolean;
  @Input() operation: string;
  @Input() idCronogramaGuardado: number;

  @ViewChild('direccionFisica') direccionFisica: DireccionFisicaComponent;
  @ViewChildren('input') vc;
  @ViewChild('eliminarAlumnoForm') eliminarAlumnoForm: NgForm;
  @ViewChild('eliminarCronogramaActivoForm') eliminarCronogramaActivoForm: NgForm;
  @ViewChild('bajaClaseForm') bajaClaseForm: NgForm;
  @ViewChild('agregarAutoForm') agregarAutoForm: NgForm;


  schedule : any;

  buttonDisabled;
  motivoDeBaja: string = '';
  math = Math;
  
  constructor(private breakpointObserver: BreakpointObserver, private cd: ChangeDetectorRef) { }

  ngOnInit() {
    console.log(this.data);
   }

  ngAfterContentChecked() {
    this.cd.detectChanges();
    this.checkDisabledConditions();
  }

  ngAfterViewInit() {
    if (this.vc.first) this.vc.first.nativeElement.focus();
  }
  
  open() {
    $('#confirmModal').modal({show: true, backdrop: 'static',keyboard: false});
  }

  onConfirm() {
    if (this.component == 'pendingConfirmationSchedules' && this.operation == 'Confirmar') {
      this.data.direccionFisicaInformation = this.direccionFisica.getData();
      this.confirmation.emit(this.data);
    } else {
      if (this.component == 'eliminarAlumno') {
        if (this.eliminarAlumnoForm.form.valid) {
          this.data.motivoDeBaja = this.motivoDeBaja;
          this.motivoDeBaja = '';
          this.eliminarAlumnoForm.form.get("motivoDeBaja").markAsUntouched();
          this.eliminarAlumnoForm.form.get("motivoDeBaja").markAsPristine();
          this.confirmation.emit(this.data);
        } else {
          this.eliminarAlumnoForm.form.get("motivoDeBaja").markAsTouched();
          this.eliminarAlumnoForm.form.get("motivoDeBaja").markAsDirty();
        }
      } else {
        if (this.component == 'pendingConfirmationSchedules' && this.operation == 'CancelarActivo') {
          if (this.eliminarCronogramaActivoForm.form.valid) {
            this.data.motivoDeBaja = this.motivoDeBaja;
            this.motivoDeBaja = '';
            this.eliminarCronogramaActivoForm.form.get("motivoDeBaja").markAsUntouched();
            this.eliminarCronogramaActivoForm.form.get("motivoDeBaja").markAsPristine();
            this.confirmation.emit(this.data);
          } else {
            this.eliminarCronogramaActivoForm.form.get("motivoDeBaja").markAsTouched();
            this.eliminarCronogramaActivoForm.form.get("motivoDeBaja").markAsDirty();
          }
        } else {
          if (this.component == 'lessons') {
            if (this.bajaClaseForm.form.valid) {
              this.data.motivoDeBaja = this.motivoDeBaja;
              this.motivoDeBaja = '';
              this.bajaClaseForm.form.get("motivoDeBaja").markAsUntouched();
              this.bajaClaseForm.form.get("motivoDeBaja").markAsPristine();
              this.confirmation.emit(this.data);
            } else {
              this.bajaClaseForm.form.get("motivoDeBaja").markAsTouched();
              this.bajaClaseForm.form.get("motivoDeBaja").markAsDirty();
            }
          } else {
            if (this.component == 'cars') {
              if (this.agregarAutoForm.form.valid) {
                this.agregarAutoForm.form.get("modeloAuto").markAsUntouched();
                this.agregarAutoForm.form.get("modeloAuto").markAsPristine();
                this.agregarAutoForm.form.get("zonaAuto").markAsUntouched();
                this.agregarAutoForm.form.get("zonaAuto").markAsPristine();
                this.agregarAutoForm.form.get("patenteAuto").markAsUntouched();
                this.agregarAutoForm.form.get("patenteAuto").markAsPristine();
                this.agregarAutoForm.form.get("colorAuto").markAsUntouched();
                this.agregarAutoForm.form.get("colorAuto").markAsPristine();
                this.confirmation.emit(this.data);
              } else {
                this.agregarAutoForm.form.get("modeloAuto").markAsTouched();
                this.agregarAutoForm.form.get("modeloAuto").markAsDirty();
                this.agregarAutoForm.form.get("zonaAuto").markAsUntouched();
                this.agregarAutoForm.form.get("zonaAuto").markAsPristine();
                this.agregarAutoForm.form.get("patenteAuto").markAsUntouched();
                this.agregarAutoForm.form.get("patenteAuto").markAsPristine();
                this.agregarAutoForm.form.get("colorAuto").markAsUntouched();
                this.agregarAutoForm.form.get("colorAuto").markAsPristine();
              }
            }
            else{
              this.confirmation.emit(this.data);
            }
          }  
        }
      }
    }
  }

  onClose() {
    $('#confirmModal').modal('hide');
  }

  resetSearch() {
    $('#confirmModal').modal('hide');
    this.close.emit(this.data);
  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }

  sendWsp() {
    const nombreAlumno = this.data[1].student_name.split(' ')[0];
    if (this.isMobile()) {
      window.open("https://wa.me/54"+this.data[2].student_phone+"?text=Hola%20"+nombreAlumno+"%2C%20te%20adjunto%20tu%20cronograma%20de%20clases%20junto%20con%20informaci%C3%B3n%20importante%2C%20por%20favor%20lee%20con%20atenci%C3%B3n%20todos%20los%20items.%0A%0A-%20Las%20clases%20no%20se%20suspenden%20por%20lluvia%20o%20movilizaciones%2C%20salvo%20que%20desde%20la%20escuela%20consideremos%20que%20es%20un%20riesgo%20salir%20a%20la%20calle.%20En%20tal%20caso%20recibir%C3%A1s%20una%20llamada%20desde%20la%20escuela%20para%20cancelar%20la%20clase.%20%0A%0A-%20Las%20clases%20no%20se%20reprograman%20a%20menos%20que%20sea%20con%20debido%20fundamento%20y%20v%C3%ADa%20llamada%20telef%C3%B3nica%20a%20la%20secretar%C3%ADa%20con%20al%20menos%2024hs%20de%20anticipaci%C3%B3n.%20%20%0A%0A-%20Recomendamos%20usar%20calzado%20cerrado%20y%20sin%20plataforma%2C%20como%20as%C3%AD%20tambi%C3%A9n%20ropa%20holgada.", "_blank");
    }
    else {
      window.open("https://web.whatsapp.com/send?phone=+54"+this.data[2].student_phone+"&text=Hola%20"+nombreAlumno+"%2C%20te%20adjunto%20tu%20cronograma%20de%20clases%20junto%20con%20informaci%C3%B3n%20importante%2C%20por%20favor%20lee%20con%20atenci%C3%B3n%20todos%20los%20items.%0A%0A-%20Las%20clases%20no%20se%20suspenden%20por%20lluvia%20o%20movilizaciones%2C%20salvo%20que%20desde%20la%20escuela%20consideremos%20que%20es%20un%20riesgo%20salir%20a%20la%20calle.%20En%20tal%20caso%20recibir%C3%A1s%20una%20llamada%20desde%20la%20escuela%20para%20cancelar%20la%20clase.%20%0A%0A-%20Las%20clases%20no%20se%20reprograman%20a%20menos%20que%20sea%20con%20debido%20fundamento%20y%20v%C3%ADa%20llamada%20telef%C3%B3nica%20a%20la%20secretar%C3%ADa%20con%20al%20menos%2024hs%20de%20anticipaci%C3%B3n.%20%20%0A%0A-%20Recomendamos%20usar%20calzado%20cerrado%20y%20sin%20plataforma%2C%20como%20as%C3%AD%20tambi%C3%A9n%20ropa%20holgada.", "_blank");
    } 
  }

  classConfirmBtn() {
    if (this.component == 'availableSchedules') {
      return 'confirmSchedule';
    } else {
      if (this.component == 'pendingConfirmationSchedules' && this.operation == 'Confirmar') {
        return 'confirmSchedule';
      }
      else {
        if (this.component == 'pendingConfirmationSchedules' && this.operation != 'Confirmar') {
          return 'cancelSchedule'
        } else {
          return 'confirmSchedule';
        }
      }
    }
  }

  checkDisabledConditions() {
    return false;
    //Mandatorio de documento y direccion fisica deshabilitados
/*     if (this.component == 'availableSchedules') {
      return false;
    } else {
      if (this.component == 'pendingConfirmationSchedules' && this.operation == 'Confirmar') {
        return (this.direccionFisica == undefined) ? false : !(this.direccionFisica.validateForm() && this.data.documento && this.data.documento.length == 10);
      }
      else {
        return false;
      }
    } */
  }

  preventLetters($event) {
    var reg = /^[0-9]+$/i;
    if (!reg.test($event.key)) {
      $event.preventDefault();
    }
  }

  setDireccionDefault() {
    this.direccionFisica.setDireccionFisicaDefault();
  }

}
