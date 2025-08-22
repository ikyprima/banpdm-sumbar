import "leaflet-routing-machine";

declare module "leaflet-routing-machine" {
  namespace Routing {
    interface RoutingControlOptions {
      createMarker?: (i: number, wp: any, nWps: number) => L.Marker;
    }
  }
}