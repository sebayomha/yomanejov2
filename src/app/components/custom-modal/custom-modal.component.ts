import { Component, Input, Output, EventEmitter } from '@angular/core';
import { BreakpointObserver } from '@angular/cdk/layout';
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

  schedule : any;

  constructor(private breakpointObserver: BreakpointObserver) { }

  ngOnInit() {
    console.log(this.data)
   }

  open() {
    $('#confirmModal').modal('show');
  }

  onConfirm() {
    this.confirmation.emit(this.data);

    console.log(this.data)
  }

  onClose() {
    $('#confirmModal').modal('hide');
    // this.close.emit(this.data);
  }

  resetSearch() {
    $('#confirmModal').modal('hide');
    this.close.emit(this.data);
  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }

  sendWsp() {
    if (this.isMobile()) {
      window.open("https://wa.me/54"+this.data[2].student_phone+"?text=Hola%2C%20te%20adjunto%20tu%20cronograma%20de%20clases%20junto%20con%20informaci%C3%B3n%20importante%2C%20por%20favor%20lee%20con%20atenci%C3%B3n%20todos%20los%20items.%0A%0A-%20Las%20clases%20no%20se%20suspenden%20por%20lluvia%20o%20movilizaciones%2C%20salvo%20que%20desde%20la%20escuela%20consideremos%20que%20es%20un%20riesgo%20salir%20a%20la%20calle.%20En%20tal%20caso%20recibir%C3%A1s%20una%20llamada%20desde%20la%20escuela%20para%20cancelar%20la%20clase.%20%0A%0A-%20Las%20clases%20no%20se%20reprograman%20a%20menos%20que%20sea%20con%20debido%20fundamento%20y%20v%C3%ADa%20llamada%20telef%C3%B3nica%20a%20la%20secretar%C3%ADa%20con%20al%20menos%2024hs%20de%20anticipaci%C3%B3n.%20%20%0A%0A-%20Recomendamos%20usar%20calzado%20cerrado%20y%20sin%20plataforma%2C%20como%20as%C3%AD%20tambi%C3%A9n%20ropa%20holgada.", "_blank");
    }
    else {
      window.open("https://web.whatsapp.com/send?phone=+54"+this.data[2].student_phone+"&text=Hola%2C%20te%20adjunto%20tu%20cronograma%20de%20clases%20junto%20con%20informaci%C3%B3n%20importante%2C%20por%20favor%20lee%20con%20atenci%C3%B3n%20todos%20los%20items.%0A%0A-%20Las%20clases%20no%20se%20suspenden%20por%20lluvia%20o%20movilizaciones%2C%20salvo%20que%20desde%20la%20escuela%20consideremos%20que%20es%20un%20riesgo%20salir%20a%20la%20calle.%20En%20tal%20caso%20recibir%C3%A1s%20una%20llamada%20desde%20la%20escuela%20para%20cancelar%20la%20clase.%20%0A%0A-%20Las%20clases%20no%20se%20reprograman%20a%20menos%20que%20sea%20con%20debido%20fundamento%20y%20v%C3%ADa%20llamada%20telef%C3%B3nica%20a%20la%20secretar%C3%ADa%20con%20al%20menos%2024hs%20de%20anticipaci%C3%B3n.%20%20%0A%0A-%20Recomendamos%20usar%20calzado%20cerrado%20y%20sin%20plataforma%2C%20como%20as%C3%AD%20tambi%C3%A9n%20ropa%20holgada.", "_blank");
    } 
    console.log("SENDWSP")
  }

}
