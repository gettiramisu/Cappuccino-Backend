{
    inputs.nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";

    outputs = {nixpkgs, ...}:
    let
        supportedSystems = ["x86_64-linux" "aarch64-linux" "x86_64-darwin" "aarch64-darwin"];
    in {
        devShells = nixpkgs.lib.genAttrs supportedSystems (system:
            let
                pkgs = nixpkgs.legacyPackages.${system};
                php = pkgs.php85.withExtensions ({enabled, all}: enabled ++ [all.xdebug]);
            in {
                default = pkgs.mkShell {
                    packages = with pkgs; [
                        symfony-cli
                        php
                        php.packages.composer
                    ];
                };
            }
        );
    };
}
